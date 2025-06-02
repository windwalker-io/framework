<?php

declare(strict_types=1);

namespace Windwalker\ORM\Hydrator;

use stdClass;
use Throwable;
use Windwalker\Database\Hydrator\FieldHydratorInterface;
use Windwalker\ORM\Attributes\Mapping;
use Windwalker\ORM\Exception\CastingException;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\ORM;
use Windwalker\Utilities\TypeCast;

/**
 * The EntityHydrator class.
 */
class EntityHydrator implements FieldHydratorInterface
{
    /**
     * EntityHydrator constructor.
     *
     * @param  FieldHydratorInterface  $hydrator
     * @param  ORM                     $orm
     */
    public function __construct(protected FieldHydratorInterface $hydrator, protected ORM $orm)
    {
    }

    /**
     * @inheritDoc
     */
    public function hydrate(array $data, object $object): object
    {
        if (!EntityMetadata::isEntity($object)) {
            return $this->hydrator->hydrate($data, $object);
        }

        $metadata = $this->orm->getEntityMetadata($object);

        $item = [];
        $props = $metadata->getProperties();
        $columns = $metadata->getColumns();

        foreach ($data as $colName => $value) {
            // Get prop name
            if ($column = $columns[$colName] ?? null) {
                // Skip if is relation field
                if ($column instanceof Mapping && is_scalar($value)) {
                    continue;
                }

                $prop = $column->getProperty();
            } else {
                $prop = $props[$colName] ?? null;
            }

            // No prop name, just assign value.
            if (!$prop) {
                $item[$colName] = $value;
                continue;
            }

            // Has prop name, cast it.
            $propName = $prop->getName();

            $item[$propName] = static::castFieldForHydrate($metadata, $colName, $value, $object);
        }

        return $this->hydrator->hydrate($item, $object);
    }

    /**
     * @inheritDoc
     */
    public function extract(object $object): array
    {
        if (!EntityMetadata::isEntity($object)) {
            return $this->hydrator->extract($object);
        }

        if ($object instanceof stdClass) {
            return get_object_vars($object);
        }

        $data = $this->hydrator->extract($object);

        $metadata = $this->orm->getEntityMetadata($object);
        $item = [];

        foreach ($metadata->getColumns() as $column) {
            $prop = $column->getProperty();

            $colName = $column->getName();
            $propName = $prop->getName();

            if (!array_key_exists($propName, $data)) {
                $propName = $colName;

                if (!array_key_exists($propName, $data)) {
                    continue;
                }
            }

            $value = $data[$propName];

            $value = static::castFieldForExtract($metadata, $colName, $value, $object);

            if (is_object($value) || is_array($value)) {
                $value = TypeCast::tryString($value);
            }

            $item[$colName] = $value;
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function extractField(object $object, string $field): mixed
    {
        if (!EntityMetadata::isEntity($object)) {
            return $this->hydrator->extractField($object, $field);
        }

        if ($object instanceof stdClass) {
            return $object->$field;
        }

        $metadata = $this->orm->getEntityMetadata($object);

        $column = $metadata->getColumn($field);

        if (!$column) {
            $prop = $field;
        } else {
            $prop = $column->getProperty()->getName();
        }

        $data = $this->hydrator->extract($object);

        if (!array_key_exists($prop, $data)) {
            throw new \UnexpectedValueException(
                'Extract an un-exists field: ' . $field . ' from: ' . get_debug_type($object)
            );
        }

        return static::castFieldForExtract(
            $metadata,
            $column->getName(),
            $data[$prop] ?? null,
            $object
        );
    }

    public static function castFieldForExtract(
        EntityMetadata $metadata,
        string $colName,
        mixed $value,
        object $entity
    ): mixed {
        $column = $metadata->getColumn($colName);

        if (!$column) {
            return $value;
        }

        $casts = $metadata->getCastManager()->getFieldCasts($colName);
        $casts = array_reverse($casts);

        foreach ($casts as [, $extractor]) {
            try {
                $value = $extractor($value, $metadata->getORM(), $entity);
            } catch (Throwable $e) {
                $castName = is_object($extractor) ? $extractor::class : json_encode($extractor);

                throw new CastingException(
                    sprintf(
                        'Error when extracting %s::$%s from value "%s" with cast: %s - %s',
                        $metadata->getClassName(),
                        $colName,
                        get_debug_type($value),
                        $castName,
                        $e->getMessage()
                    ),
                    $e->getCode(),
                    $e
                );
            }
        }

        return $value;
    }

    public static function castFieldForHydrate(
        EntityMetadata $metadata,
        string $colName,
        mixed $value,
        object $entity
    ): mixed {
        $column = $metadata->getColumn($colName);

        // If this column name not exists, maybe this is property name.
        if (!$column) {
            $column = $metadata->getColumnByPropertyName($colName);

            if (!$column) {
                return $value;
            }

            $colName = $column->getName();
        }

        $casts = $metadata->getCastManager()->getFieldCasts($colName);

        if ($casts === []) {
            return $value;
        }

        foreach ($casts as [$hydrator]) {
            try {
                $value = $hydrator($value, $metadata->getORM(), $entity);
            } catch (Throwable $e) {
                if ($hydrator instanceof \Closure) {
                    $hydrator = static::extractCastNameFromClosure($hydrator);
                }

                $castName = is_object($hydrator) ? $hydrator::class : json_encode($hydrator);

                throw new CastingException(
                    sprintf(
                        'Error when hydrating %s::$%s from value "%s" to "%s" with cast: %s - %s',
                        $metadata->getClassName(),
                        $colName,
                        get_debug_type($value),
                        $column->getProperty()->getType(),
                        $castName,
                        $e->getMessage()
                    ),
                    $e->getCode(),
                    $e
                );
            }
        }

        return $value;
    }

    /**
     * castArray
     *
     * @param  EntityMetadata  $metadata
     * @param  array           $data
     *
     * @return  array
     *
     * @internal
     */
    public static function castArray(EntityMetadata $metadata, array $data, ?object $entity = null): array
    {
        $entity ??= $metadata->getEntityMapper()->toEntity($data);

        foreach ($data as $k => $datum) {
            $data[$k] = static::castFieldForExtract($metadata, $k, $datum, $entity);
        }

        return $data;
    }

    /**
     * @param  \Closure  $hydrator
     *
     * @return  mixed
     *
     * @throws \ReflectionException
     */
    protected static function extractCastNameFromClosure(\Closure $hydrator): mixed
    {
        $ref = new \ReflectionFunction($hydrator);

        if (($caster = $ref->getClosureUsedVariables()['caster'] ?? null) && $caster instanceof \Closure) {
            $ref = new \ReflectionFunction($caster);

            if ($cast = $ref->getClosureUsedVariables()['cast'] ?? null) {
                $hydrator = $cast;
            }
        }

        return $hydrator;
    }
}
