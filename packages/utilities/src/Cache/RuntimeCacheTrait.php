<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Cache;

/**
 * The RuntimeCacheTrait class.
 */
trait RuntimeCacheTrait
{
    protected static array $cacheStorage = [];

    protected static function cacheGet(string $id)
    {
        return static::$cacheStorage[$id] ?? null;
    }

    protected static function cacheSet(string $id, $value = null): void
    {
        static::$cacheStorage[$id] = $value;
    }

    protected static function cacheHas(string $id): bool
    {
        return isset(static::$cacheStorage[$id]);
    }

    public static function cacheRemove(string $id): static
    {
        unset(static::$cacheStorage[$id]);
    }

    public static function cacheReset(): void
    {
        static::$cacheStorage = [];
    }

    protected static function once(?string $id, callable $closure, bool $refresh = false)
    {
        if ($refresh) {
            unset(static::$cacheStorage[$id]);
        }

        return static::$cacheStorage[$id] ??= $closure();
    }
}
