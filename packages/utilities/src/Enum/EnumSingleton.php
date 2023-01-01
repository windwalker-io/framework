<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

use MyCLabs\Enum\Enum;

/**
 * The EnumSingleton class.
 */
class EnumSingleton extends Enum
{
    /**
     * @param mixed $value
     * @return static
     */
    public static function from($value): self
    {
        $key = static::assertValidValueReturningKey($value);

        return static::__callStatic($key, []);
    }

    /**
     * Asserts valid enum value
     *
     * @psalm-pure
     * @psalm-assert T $value
     * @param mixed $value
     * @return string
     */
    protected static function assertValidValueReturningKey($value): string
    {
        if (false === ($key = static::search($value))) {
            throw new \UnexpectedValueException("Value '$value' is not part of the enum " . static::class);
        }

        return $key;
    }

    /**
     * @inheritDoc
     */
    public static function __callStatic($name, $arguments)
    {
        return self::$instances[static::class][$name] ??= parent::__callStatic($name, $arguments);
    }
}
