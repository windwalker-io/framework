<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Accessible;

trait RecordOptionsTrait
{
    public static function wrap(array|self|null $values): static
    {
        if ($values instanceof static) {
            return $values;
        }

        if ($values === null) {
            $values = [];
        }

        return new static(...$values);
    }

    public static function tryWrap(mixed $values): ?static
    {
        if ($values === null) {
            return null;
        }

        return static::wrap(...$values);
    }

    public function merge(...$values): static
    {
        foreach ($values as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    public function with(...$values): static
    {
        return (clone $this)->merge(...$values);
    }
}
