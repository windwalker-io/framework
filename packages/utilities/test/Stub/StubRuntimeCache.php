<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Stub;

use Windwalker\Utilities\Cache\RuntimeCacheTrait;

/**
 * Exposes the protected static API of RuntimeCacheTrait for RuntimeCacheTraitTest.
 *
 * Can be removed together with RuntimeCacheTraitTest if the trait is removed.
 */
class StubRuntimeCache
{
    use RuntimeCacheTrait;

    public static function get(string $id): mixed
    {
        return static::cacheGet($id);
    }

    public static function set(string $id, mixed $value): void
    {
        static::cacheSet($id, $value);
    }

    public static function has(string $id): bool
    {
        return static::cacheHas($id);
    }

    public static function run(string $id, callable $closure, bool $refresh = false): mixed
    {
        return static::once($id, $closure, $refresh);
    }
}
