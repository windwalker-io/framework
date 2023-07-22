<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Data;

use IteratorAggregate;
use Windwalker\Attributes\AttributesAccessor;
use Windwalker\Database\Hydrator\SimpleHydrator;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\CastNullable;
use Windwalker\Utilities\Contract\ArrayAccessibleInterface;
use Windwalker\Utilities\Contract\DumpableInterface;
use Windwalker\Utilities\StrNormalize;
use Windwalker\Utilities\TypeCast;

/**
 * The ValueObject class.
 */
#[\AllowDynamicProperties]
class ValueObject implements
    ArrayAccessibleInterface,
    DumpableInterface,
    IteratorAggregate,
    \Stringable,
    \JsonSerializable
{
    public function __construct(mixed $data = null)
    {
        $this->fill($data);
    }

    public static function wrap(mixed $data): static
    {
        if ($data instanceof static) {
            return $data;
        }

        return new static($data);
    }

    public function fill(mixed $data): static
    {
        if (is_string($data) && is_json($data)) {
            $data = json_decode($data, true);
        }

        $values = TypeCast::toArray($data);

        foreach ($values as $key => $value) {
            if (!property_exists($this, (string) $key)) {
                if (str_contains((string) $key, '_')) {
                    $key = StrNormalize::toCamelCase((string) $key);
                } else {
                    $key = StrNormalize::toSnakeCase((string) $key);
                }
            }

            // Haydrating
            $this->hydrateField($key, $value);
        }

        return $this;
    }

    private function hydrateField(string $key, mixed $value): void
    {
        $setter = 'set' . StrNormalize::toPascalCase($key);

        if (method_exists($this, $setter)) {
            $this->$setter($value);
            return;
        }

        if (!property_exists($this, $key)) {
            $this->$key = $value;
            return;
        }

        $attrs = AttributesAccessor::getAttributesFromAny(
            new \ReflectionProperty($this, $key),
            Cast::class,
            \ReflectionAttribute::IS_INSTANCEOF
        );

        foreach ($attrs as $attr) {
            /**
             * @var Cast $attrInstance
             */
            $attrInstance = $attr->newInstance();

            if ($attrInstance instanceof CastNullable && $value === null) {
                continue;
            }

            $hydrateTarget = $attrInstance->getHydrate();

            if (class_exists($hydrateTarget)) {
                $value = new $hydrateTarget($value);
            } elseif (is_callable($hydrateTarget)) {
                $value = $hydrateTarget($value);
            }
        }

        $this->$key = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists(mixed $key): bool
    {
        return property_exists($this, $key);
    }

    /**
     * @inheritDoc
     */
    public function &offsetGet(mixed $key): mixed
    {
        return $this->$key;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->$key = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset(mixed $key): void
    {
        unset($this->$key);
    }

    /**
     * @inheritDoc
     */
    public function dump(bool $recursive = false, bool $onlyDumpable = false): array
    {
        return TypeCast::toArray(get_object_vars($this), $recursive, $onlyDumpable);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->dump());
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        return $this->dump();
    }

    public function __toString(): string
    {
        return json_encode($this);
    }
}
