<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Proxy;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Proxy\CachedCallable;
use Windwalker\Utilities\Proxy\CallableProxy;
use Windwalker\Utilities\Proxy\ChainableCallable;
use Windwalker\Utilities\Proxy\DisposableCallable;
use Windwalker\Utilities\Proxy\StringableCallable;
use Windwalker\Utilities\Proxy\TimesLimitedCallable;

class CallableProxyTest extends TestCase
{
    /**
     * @see  CallableProxy::__invoke
     */
    #[Test]
    public function invokeForwardsArgumentsToCallable(): void
    {
        $proxy = new CallableProxy(static fn(int $a, int $b) => $a + $b);

        self::assertSame(5, $proxy(2, 3));
    }

    /**
     * @see  CallableProxy::get
     * @see  CallableProxy::unwrap
     */
    #[Test]
    public function getAndUnwrapResolveNestedProxies(): void
    {
        $fn = static fn() => 'x';
        $inner = new CallableProxy($fn);
        $outer = new CallableProxy($inner);

        self::assertSame($inner, $outer->get());
        self::assertSame($fn, $outer->get(true));
        self::assertSame($fn, CallableProxy::unwrap($outer));
        self::assertSame($fn, CallableProxy::unwrap($fn));
    }

    /**
     * @see  CachedCallable::__invoke
     */
    #[Test]
    public function cachedCallableRunsOnceAndReturnsCachedValue(): void
    {
        $calls = 0;
        $cached = new CachedCallable(
            static function (int $n) use (&$calls) {
                $calls++;

                return $n * 2;
            }
        );

        self::assertSame(4, $cached(2));
        self::assertSame(4, $cached(10));
        self::assertSame(1, $calls);
    }

    /**
     * @see  ChainableCallable::__invoke
     * @see  ChainableCallable::chain
     * @see  ChainableCallable::getQueue
     */
    #[Test]
    public function chainableCallablePipesResultThroughQueue(): void
    {
        $proxy = new ChainableCallable(static fn(int $a, int $b) => $a + $b);
        $double = static fn(int $v) => $v * 2;
        $inc = static fn(int $v) => $v + 1;

        self::assertSame($proxy, $proxy->chain($double)->chain($inc));
        self::assertSame([$double, $inc], $proxy->getQueue());
        self::assertSame(11, $proxy(2, 3));
    }

    /**
     * @see  DisposableCallable::__invoke
     * @see  DisposableCallable::isCalled
     */
    #[Test]
    public function disposableCallableOnlyRunsOnce(): void
    {
        $calls = 0;
        $proxy = new DisposableCallable(
            static function () use (&$calls) {
                return ++$calls;
            }
        );

        self::assertFalse($proxy->isCalled());
        self::assertSame(1, $proxy());
        self::assertTrue($proxy->isCalled());
        self::assertNull($proxy());
        self::assertSame(1, $calls);
    }

    /**
     * @see  StringableCallable::__toString
     */
    #[Test]
    public function stringableCallableCastsResultToString(): void
    {
        self::assertSame('42', (string) new StringableCallable(static fn() => 42));
    }

    /**
     * @see  TimesLimitedCallable::__invoke
     * @see  TimesLimitedCallable::isOverLimits
     * @see  TimesLimitedCallable::getCallTimes
     */
    #[Test]
    public function timesLimitedCallableStopsAfterLimit(): void
    {
        $proxy = new TimesLimitedCallable(static fn(string $s) => strtoupper($s), 2);

        self::assertFalse($proxy->isOverLimits());
        self::assertSame('A', $proxy('a'));
        self::assertSame('B', $proxy('b'));
        self::assertTrue($proxy->isOverLimits());
        self::assertNull($proxy('c'));
        self::assertSame(2, $proxy->getCallTimes());
    }

    /**
     * @see  TimesLimitedCallable::isOverLimits
     */
    #[Test]
    public function timesLimitedCallableWithoutLimitNeverStops(): void
    {
        $proxy = new TimesLimitedCallable(static fn() => 'ok');

        for ($i = 0; $i < 5; $i++) {
            self::assertSame('ok', $proxy());
        }

        self::assertFalse($proxy->isOverLimits());
        self::assertNull($proxy->getLimits());
    }
}
