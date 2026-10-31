<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Promise;

use Exception;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Windwalker\Http\Promise\PromiseResponse;

/**
 * Test class of PromiseResponse
 */
class PromiseResponseTest extends TestCase
{
    #[Test]
    public function resolveReturnsValueUnchangedWithoutCallbacks(): void
    {
        self::assertSame('foo', new PromiseResponse()->resolve('foo'));
    }

    #[Test]
    public function resolvePipesValueThroughThenCallbacksInOrder(): void
    {
        $promise = new PromiseResponse()
            ->then(fn(int $v) => $v + 1)
            ->then(fn(int $v) => $v * 10);

        self::assertSame(20, $promise->resolve(1));
    }

    #[Test]
    public function resolveCallsFailCallbacksWhenThenThrowsAndStopsChain(): void
    {
        $caught = [];
        $afterThrow = false;

        $promise = new PromiseResponse()
            ->then(static fn($v) => throw new RuntimeException('boom'))
            ->then(function ($v) use (&$afterThrow) {
                $afterThrow = true;

                return $v;
            })
            ->fail(function (Exception $e) use (&$caught) {
                $caught[] = $e->getMessage();

                return $e;
            });

        $result = $promise->resolve('orig');

        self::assertSame(['boom'], $caught);
        self::assertFalse($afterThrow);
        self::assertSame('orig', $result);
    }

    #[Test]
    public function resolveDoesNotCatchNonExceptionThrowables(): void
    {
        $called = false;
        $promise = new PromiseResponse()
            ->then(static fn($v) => throw new \Error('fatal'))
            ->fail(function ($e) use (&$called) {
                $called = true;

                return $e;
            });

        try {
            $promise->resolve(1);
            self::fail('Error should propagate');
        } catch (\Error $e) {
            self::assertSame('fatal', $e->getMessage());
        }

        self::assertFalse($called);
    }

    #[Test]
    public function rejectReturnsValueUnchangedWithoutCallbacks(): void
    {
        $e = new Exception('x');

        self::assertSame($e, new PromiseResponse()->reject($e));
    }

    #[Test]
    public function rejectPipesValueThroughFailCallbacks(): void
    {
        $promise = new PromiseResponse()
            ->fail(fn($e) => $e . 'a')
            ->fail(fn($e) => $e . 'b');

        self::assertSame('xab', $promise->reject('x'));
    }

    #[Test]
    public function thenAndFailReturnSameInstanceForChaining(): void
    {
        $promise = new PromiseResponse();

        self::assertSame($promise, $promise->then(fn($v) => $v));
        self::assertSame($promise, $promise->fail(fn($v) => $v));
    }
}
