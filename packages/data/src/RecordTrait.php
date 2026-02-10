<?php

declare(strict_types=1);

namespace Windwalker\Data;

use Windwalker\ORM\Attributes\JsonNoSerialize;
use Windwalker\ORM\Attributes\JsonSerializerInterface;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Attributes\AttributesAccessor;
use Windwalker\Utilities\Classes\TraitHelper;
use Windwalker\Utilities\TypeCast;

use function Windwalker\get_object_dump_values;

trait RecordTrait
{
    public static function wrap(mixed $values, bool $clone = false): static
    {
        if ($values instanceof static) {
            if ($clone) {
                $values = clone $values;
            }

            return $values;
        }

        if ($values === null) {
            $values = [];
        }

        $values = TypeCast::toArray($values);
        $args = [];

        $ref = new \ReflectionClass(static::class);
        $constructor = $ref->getConstructor();

        if ($constructor) {
            foreach ($constructor->getParameters() as $parameter) {
                $name = $parameter->getName();

                if (array_key_exists($name, $values)) {
                    $args[$name] = $values[$name];

                    unset($values[$name]);
                }
            }
        }

        return new static(...$args)->merge($values);
    }

    public static function wrapWith(...$values): static
    {
        return static::wrap($values, true);
    }

    public static function tryWrap(mixed $values, bool $clone = false): ?static
    {
        if ($values === null) {
            return null;
        }

        return static::wrap($values, $clone);
    }

    public static function tryWrapWith(...$data): ?static
    {
        return static::tryWrap($data, true);
    }

    public function fill(mixed $data): static
    {
        $values = TypeCast::toArray($data);

        foreach ($values as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    public function merge(mixed $values, bool $recursive = false, bool $ignoreNulls = false): static
    {
        $values = TypeCast::toArray($values);

        foreach ($values as $key => $value) {
            if (
                $recursive
                && is_object($this->$key ?? null)
                && TraitHelper::uses($this->$key, RecordTrait::class)
            ) {
                $this->$key = $this->$key->withMerge($value, true, $ignoreNulls);
            } elseif ($recursive && is_array($this->$key ?? null) && is_array($value)) {
                $this->$key = Arr::mergeRecursive($this->$key, $value);
            } elseif ($ignoreNulls && $value === null) {
                continue;
            } else {
                $this->$key = $value;
            }
        }

        return $this;
    }

    public function withMerge(mixed $values, bool $recursive = false, bool $ignoreNulls = false): static
    {
        return (clone $this)->merge($values, $recursive, $ignoreNulls);
    }

    public function defaults(mixed $values, bool $recursive = false): static
    {
        $new = clone $this;

        return $this->merge($values, $recursive)->merge($new, $recursive, true);
    }

    public function withDefaults(mixed $values, bool $recursive = false): static
    {
        $new = clone $this;

        return (clone $this)->merge($values, $recursive)->merge($new, $recursive, true);
    }

    public function with(...$data): static
    {
        if (PHP_VERSION_ID >= 80500) {
            include_once __DIR__ . '/clone_with.php';

            return cloneWithPolyfill($this, $data);
        }

        if (method_exists($this, 'cloneWith')) {
            return $this->cloneWith($data);
        }

        $new = clone $this;

        return $new->fill($data);
    }

    public function dump(bool $recursive = false, bool $onlyDumpable = false): array
    {
        return TypeCast::toArray(get_object_dump_values($this), $recursive, $onlyDumpable);
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $className
     *
     * @return T
     */
    public function as(string $className): object
    {
        if ($this instanceof $className) {
            return $this;
        }

        return $className::wrap($this);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        $item = $this->dump();

        foreach ($item as $key => $value) {
            $prop = new \ReflectionProperty($this, $key);
            $attrs = AttributesAccessor::getAttributesFromAny(
                $prop,
                JsonSerializerInterface::class,
                \ReflectionAttribute::IS_INSTANCEOF
            );

            /** @var \ReflectionAttribute<JsonSerializerInterface> $attr */
            foreach ($attrs as $attr) {
                if (is_a($attr->getName(), JsonNoSerialize::class, true)) {
                    unset($item[$key]);
                    continue 2;
                }

                $attrInstance = $attr->newInstance();

                $value = $attrInstance->serialize($value);
            }

            $item[$key] = $value;
        }

        return $item;
    }
}
