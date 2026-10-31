<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Cache;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Cache\InstanceCacheTrait;

class InstanceCacheTraitTest extends TestCase
{
    protected object $instance;

    #[Before]
    protected function createInstance(): void
    {
        $this->instance = new class () {
            use InstanceCacheTrait {
                cacheGet as public;
                cacheSet as public;
                cacheHas as public;
                once as public;
            }
        };
    }

    #[Test]
    public function cacheSetStoresAndReturnsValue(): void
    {
        self::assertSame('foo', $this->instance->cacheSet('a', 'foo'));
        self::assertSame('foo', $this->instance->cacheGet('a'));
        self::assertTrue($this->instance->cacheHas('a'));
    }

    #[Test]
    public function cacheGetReturnsNullForMissingKey(): void
    {
        self::assertNull($this->instance->cacheGet('missing'));
        self::assertFalse($this->instance->cacheHas('missing'));
    }

    #[Test]
    public function cacheHasIsFalseForStoredNull(): void
    {
        $this->instance->cacheSet('a', null);

        self::assertFalse($this->instance->cacheHas('a'));
    }

    #[Test]
    public function cacheRemoveDeletesOnlyTheGivenKey(): void
    {
        $this->instance->cacheSet('a', 1);
        $this->instance->cacheSet('b', 2);

        self::assertSame($this->instance, $this->instance->cacheRemove('a'));
        self::assertFalse($this->instance->cacheHas('a'));
        self::assertTrue($this->instance->cacheHas('b'));
    }

    #[Test]
    public function cacheResetClearsEverything(): void
    {
        $this->instance->cacheSet('a', 1);
        $this->instance->cacheSet('b', 2);

        self::assertSame($this->instance, $this->instance->cacheReset());
        self::assertFalse($this->instance->cacheHas('a'));
        self::assertFalse($this->instance->cacheHas('b'));
    }

    #[Test]
    public function onceRunsClosureOnlyOnceUnlessRefreshed(): void
    {
        $count = 0;
        $closure = function () use (&$count) {
            return ++$count;
        };

        self::assertSame(1, $this->instance->once('k', $closure));
        self::assertSame(1, $this->instance->once('k', $closure));
        self::assertSame(2, $this->instance->once('k', $closure, true));
        self::assertSame(2, $this->instance->once('k', $closure));
    }

    #[Test]
    public function onceCachesNullResult(): void
    {
        $count = 0;
        $closure = function () use (&$count) {
            $count++;

            return null;
        };

        $this->instance->once('k', $closure);
        $this->instance->once('k', $closure);

        self::assertSame(1, $count);
    }

    #[Test]
    public function cacheIsSeparatedPerInstance(): void
    {
        $this->instance->cacheSet('a', 1);

        self::assertTrue($this->instance->cacheHas('a'));
        self::assertFalse((new (get_class($this->instance)))->cacheHas('a'));
    }
}
