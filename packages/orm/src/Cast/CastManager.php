<?php

declare(strict_types=1);

namespace Windwalker\ORM\Cast;

use InvalidArgumentException;
use Windwalker\Data\RecordInterface;
use Windwalker\Data\RecordTrait;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\CastAttributeInterface;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\ORM;
use Windwalker\Utilities\Cache\InstanceCacheTrait;
use Windwalker\Utilities\Classes\TraitHelper;
use Windwalker\Utilities\Contract\DumpableInterface;
use Windwalker\Utilities\Enum\EnumExtendedInterface;
use Windwalker\Utilities\Enum\EnumExtendedTrait;
use Windwalker\Utilities\Enum\EnumPhpAdapterTrait;
use Windwalker\Utilities\Enum\EnumSingleton;
use Windwalker\Utilities\TypeCast;

/**
 * The CastManager class.
 */
class CastManager
{
    use InstanceCacheTrait;

    /**
     * @var array<int, array<int, array<int, callable|int>>>
     */
    protected array $castGroups = [];

    /**
     * @var array<int, mixed>
     */
    protected array $castAliases = [];

    /**
     * CastManager constructor.
     */
    public function __construct()
    {
        $this->prepareDefaultAliases();
    }

    /**
     * Add a custom cast type, the field must be DB field name.
     *
     * @param  string      $field
     * @param  mixed       $hydrate
     * @param  mixed|null  $extract
     * @param  int         $options
     *
     * @return  static
     */
    public function addCast(
        string $field,
        mixed $hydrate,
        mixed $extract = null,
        int $options = 0
    ): static {
        $this->castGroups[$field] ??= [];

        $this->castGroups[$field][] = [$hydrate, $extract, $options];

        return $this;
    }

    /**
     * getCast
     *
     * @param  string  $field
     *
     * @return  array<array<callable|object>>
     */
    public function getFieldCasts(string $field): array
    {
        return $this->once(
            'casts:' . $field,
            function () use ($field) {
                $groups = $this->castGroups[$field] ?? [];
                $casts = [];

                foreach ($groups as $castControl) {
                    [$cast, $extract, $options] = $castControl;

                    if (!$extract) {
                        if ($cast instanceof CastInterface || is_subclass_of($cast, CastInterface::class)) {
                            $extract = $cast;
                        } else {
                            $extract = $this->getDefaultExtractHandler($cast, $options);
                        }
                    }

                    $casts[] = [
                        $this->wrapCastCallback(
                            $this->castToCallback($cast, $options, 'hydrate'),
                            $options
                        ),
                        $this->wrapCastCallback(
                            $this->castToCallback($extract, $options, 'extract'),
                            $options
                        ),
                    ];
                }

                return $casts;
            }
        );
    }

    /**
     * @param  array  $castGroups
     *
     * @return  static  Return self to support chaining.
     */
    public function setCastGroups(array $castGroups): static
    {
        $this->castGroups = $castGroups;

        return $this;
    }

    public function alias(string $castName, mixed $alias): static
    {
        $this->castAliases[$castName] = $alias;

        return $this;
    }

    public function removeAlias(string $castName): static
    {
        unset($this->castAliases[$castName]);

        return $this;
    }

    public function setAliases(array $aliases): static
    {
        $this->castAliases = $aliases;

        return $this;
    }

    public function resolveAlias(string $castName): mixed
    {
        while (isset($this->castAliases[$castName])) {
            $castName = $this->castAliases[$castName];
        }

        return $castName;
    }

    /**
     * castToCallback
     *
     * @param  mixed   $cast
     * @param  int     $options
     * @param  string  $direction
     *
     * @return  callable
     */
    public function castToCallback(mixed $cast, int $options, string $direction = 'hydrate'): callable
    {
        if ($cast === null) {
            return static fn(mixed $value) => $value;
        }

        if (is_object($cast)) {
            // Cast interface
            if ($cast instanceof CastInterface) {
                return $cast->$direction(...);
            }

            // If object has __invoke method, we can use it directly
            if (is_callable($cast)) {
                return $cast;
            }

            // Pure object
            return static fn(mixed $value, ORM $orm) => $orm->getDb()
                ->getHydrator()
                ->hydrate($value, $cast);
        }

        // For string and array callable.
        if (is_callable($cast)) {
            return $cast;
        }

        if (is_string($cast)) {
            $cast = $this->resolveAlias($cast);

            if (class_exists($cast)) {
                // Cast interface
                if (is_subclass_of($cast, CastInterface::class)) {
                    return static function (
                        mixed $value,
                        ORM $orm,
                        CasterInfo $info
                    ) use (
                        $cast,
                        $options,
                        $direction
                    ) {
                        $resolver = $orm->getAttributesResolver();

                        return $resolver->call(
                            $resolver->createObject($cast)->$direction(...),
                            [
                                $value,
                                'value' => $value,
                                'orm' => $orm,
                                'column' => $info->column,
                                'info' => $info,
                                ORM::class => $orm,
                                Column::class => $info->column,
                                CasterInfo::class => $info,
                            ]
                        );
                    };
                }

                // Pure class
                return static function (mixed $value, ORM $orm) use ($cast, $options) {
                    if (is_subclass_of($cast, \BackedEnum::class)) {
                        if (
                            is_a($cast, EnumExtendedInterface::class, true)
                            || TraitHelper::uses($cast, EnumExtendedTrait::class)
                            || TraitHelper::uses($cast, EnumPhpAdapterTrait::class)
                        ) {
                            return $cast::wrap($value);
                        }

                        return $cast::from($value);
                    }

                    if (is_subclass_of($cast, EnumSingleton::class)) {
                        return $cast::wrap($value);
                    }

                    if (
                        is_subclass_of($cast, RecordInterface::class)
                        || TraitHelper::uses($cast, RecordTrait::class)
                    ) {
                        return $cast::wrap($value);
                    }

                    if (!($options & Cast::USE_HYDRATOR) && !($options & Cast::USE_CONSTRUCTOR)) {
                        $options |= EntityMetadata::isEntity($cast)
                            ? Cast::USE_HYDRATOR
                            : Cast::USE_CONSTRUCTOR;
                    }

                    if ($options & Cast::USE_HYDRATOR) {
                        $object = $orm->getAttributesResolver()->createObject($cast);

                        $value = TypeCast::toArray($value);

                        if (EntityMetadata::isEntity($object)) {
                            return $orm->getEntityHydrator()->hydrate($value, $object);
                        }

                        return $orm->getDb()->getHydrator()->hydrate($value, $object);
                    }

                    return $orm->getAttributesResolver()->createObject($cast, $value);
                };
            }

            return static function (mixed $value) use ($options, $cast) {
                return TypeCast::try($value, $cast);
            };
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unsupported cast type: %s',
                get_debug_type($cast)
            )
        );
    }

    public function wrapCastCallback(callable $caster, int $options): \Closure
    {
        return function (
            mixed $value,
            ORM $orm,
            ?object $entity = null,
            bool $isNew = false,
            ?Column $column = null,
        ) use (
            $options,
            $caster
        ) {
            if ($value === '' && ($options & CastAttributeInterface::EMPTY_STRING_TO_NULL)) {
                $value = null;
            }

            if ($this->shouldReturn($value, $options)) {
                return $value;
            }

            $info = new CasterInfo(
                entity: $entity,
                isNew: $isNew,
                field: $column?->getName() ?? '',
                value: $value,
                column: $column,
                orm: $orm,
            );

            return $orm->getAttributesResolver()
                ->call(
                    $caster,
                    [
                        $value,
                        'value' => $value,
                        'orm' => $orm,
                        'entity' => $entity,
                        'isNew' => $isNew,
                        'column' => $column,
                        'info' => $info,
                        $entity::class => $entity,
                        ORM::class => $orm,
                        Column::class => $column,
                        CasterInfo::class => $info,
                    ]
                );
        };
    }

    public function shouldReturn(mixed $value, int $options): bool
    {
        if ($value === null && $options & Cast::NULLABLE) {
            return true;
        }

        if ($value !== null && $options & Cast::DEFAULT_IF_NULL) {
            return true;
        }

        if (!empty($value) && $options & Cast::DEFAULT_IF_EMPTY) {
            return true;
        }

        return false;
    }

    protected function prepareDefaultAliases(): void
    {
        $this->alias(
            'datetime',
            DateTimeCast::class
        );

        $this->alias(
            'timestamp',
            TimestampCast::class
        );

        $this->alias(
            'json',
            JsonCast::class
        );

        $this->alias(
            'uuid',
            UuidCast::class
        );

        $this->alias(
            'uuid_bin',
            UuidBinCast::class
        );
    }

    /**
     * @param  mixed  $cast
     * @param  int    $options
     *
     * @return  mixed
     */
    public function getDefaultExtractHandler(mixed $cast, int $options): mixed
    {
        if (is_subclass_of($cast, \DateTimeInterface::class, true)) {
            return static function (mixed $value, ORM $orm) {
                if ($value instanceof \DateTimeInterface) {
                    return $value->format($orm->getDb()->getDateFormat());
                }

                return (string) $value;
            };
        }

        if (is_subclass_of($cast, DumpableInterface::class, true)) {
            return static fn(mixed $value) => $value;
        }

        return [TypeCast::class, 'tryString'];
    }
}
