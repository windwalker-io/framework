<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Cache;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Test\Stub\StubRuntimeCache;

class RuntimeCacheTraitTest extends TestCase
{
    #[Before]
    #[After]
    protected function resetCache(): void
    {
        StubRuntimeCache::cacheReset();
    }

    #[Test]
    public function cacheSetStoresValueForGetAndHas(): void
    {
        StubRuntimeCache::set('a', 'foo');

        self::assertSame('foo', StubRuntimeCache::get('a'));
        self::assertTrue(StubRuntimeCache::has('a'));
        self::assertNull(StubRuntimeCache::get('missing'));
        self::assertFalse(StubRuntimeCache::has('missing'));
    }

    #[Test]
    public function cacheRemoveDeletesOnlyTheGivenKey(): void
    {
        StubRuntimeCache::set('a', 1);
        StubRuntimeCache::set('b', 2);

        StubRuntimeCache::cacheRemove('a');

        self::assertFalse(StubRuntimeCache::has('a'));
        self::assertTrue(StubRuntimeCache::has('b'));
    }

    #[Test]
    public function cacheResetClearsEverything(): void
    {
        StubRuntimeCache::set('a', 1);

        StubRuntimeCache::cacheReset();

        self::assertFalse(StubRuntimeCache::has('a'));
    }

    #[Test]
    public function onceRunsClosureOnlyOnceUnlessRefreshed(): void
    {
        $count = 0;
        $closure = function () use (&$count) {
            return ++$count;
        };

        self::assertSame(1, StubRuntimeCache::run('k', $closure));
        self::assertSame(1, StubRuntimeCache::run('k', $closure));
        self::assertSame(2, StubRuntimeCache::run('k', $closure, true));
        self::assertSame(2, StubRuntimeCache::run('k', $closure));
    }
}
