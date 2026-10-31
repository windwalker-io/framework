<?php

declare(strict_types=1);

namespace Windwalker\Scalars;

class ScalarsFactory
{
    public static function fromNative(mixed $value): mixed
    {
        if (is_array($value)) {
            return new ArrayObject($value);
        }

        if (is_scalar($value)) {
            return new StringObject($value);
        }

        return $value;
    }
}
