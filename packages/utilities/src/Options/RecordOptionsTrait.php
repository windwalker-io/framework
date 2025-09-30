<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Options;

use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Classes\TraitHelper;
use Windwalker\Utilities\StrNormalize;
use Windwalker\Utilities\TypeCast;

trait RecordOptionsTrait
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

    public static function wrapWith(mixed $values): static
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

    public function merge(mixed $values, bool $recursive = false, bool $ignoreNulls = false): static
    {
        $values = TypeCast::toArray($values);

        foreach ($values as $key => $value) {
            $key = $this->normalizeKey($key);

            if (
                $recursive
                && is_object($this->$key)
                && TraitHelper::uses($this->$key, RecordOptionsTrait::class)
            ) {
                $this->$key->merge($value, true, $ignoreNulls);
            } elseif ($recursive && is_array($this->$key) && is_array($value)) {
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

    public function with(...$values): static
    {
        return $this->withMerge($values);
    }

    protected function normalizeKey(int|string $key): string|int
    {
        static $camelCaches = [];

        if (!property_exists($this, $key) && str_contains($key, '_')) {
            $key = $camelCaches[$key] ??= StrNormalize::toCamelCase($key);
        }

        return $key;
    }
}
