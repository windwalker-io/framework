<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

use MyCLabs\Enum\Enum;

/**
 * The EnumSingleton class.
 *
 * @deprecated  Use 8.2 enums
 */
class EnumSingleton extends Enum
{
    /**
     * @param mixed $value
     * @return static
     */
    public static function from($value): self
    {
        return static::__callStatic(static::search($value), []);
    }

    /**
     * @inheritDoc
     */
    public static function __callStatic($name, $arguments)
    {
        return self::$instances[static::class][$name] ??= (function () use ($name) {
            $value = static::toArray()[$name] ?? $name;

            return new static($value);
        })();
    }
}
