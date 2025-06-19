<?php

declare(strict_types=1);

namespace Windwalker\Promise\Test;

use Exception;
use Windwalker\Promise\Enum\PromiseState;
use Windwalker\Promise\Exception\UncaughtException;
use Windwalker\Promise\Exception\UnsettledException;
use Windwalker\Promise\Promise;
use Windwalker\Promise\Scheduler\DeferredScheduler;
use Windwalker\Promise\Scheduler\ImmediateScheduler;
use Windwalker\Promise\Scheduler\TaskQueue;
use Windwalker\Promise\SettledResult;
use Windwalker\Utilities\Reflection\ReflectAccessor;

use function Windwalker\Promise\await;
use function Windwalker\Promise\resolve;

/**
 * The PromiseTest class.
 */
class PromiseTest extends AbstractPromiseTestCase
{
    public function testConstructorAndRun(): void
    {
        $foo = null;

        $p = new Promise(
            function () use (&$foo) {
                $foo = 'Hello';
            }
        );

        // Promise constructor should be sync
        self::assertEquals('Hello', $foo);
    }

    public function testConstructorResolve(): void
    {
        // Resolve with value
        $p = new Promise(
            function ($resolve) {
                $resolve('Flower');
            }
        );

        self::assertEquals(PromiseState::FULFILLED, $p->getState());
        self::assertEquals('Flower', $p->wait());
    }

    public function testEmptyConstructorThen(): void
    {
        [$p1, $resolve] = Promise::withResolvers();
        $p2 = $p1->then(fn ($v) => $v);

        $resolve('Flower');

        $v = $p2->wait();

        self::assertEquals(PromiseState::FULFILLED, $p2->getState());
        self::assertEquals('Flower', $v);
    }

    public function testWaitUnResolved(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Running empty TaskQueue is not allowed, this may cause Promise lock.');

        $p = new Promise();
        $p = $p->then(fn ($v) => $v);

        $p->wait();
    }

    public function testConstructorResolveAnotherPromise(): void
    {
        $p1 = new Promise(
            function ($resolve) {
                $resolve('Sakura');
            }
        );

        self::assertEquals(PromiseState::FULFILLED, $p1->getState());

        $p = new Promise(
            function ($resolve) use ($p1) {
                $resolve($p1);
            }
        );

        self::assertEquals(PromiseState::FULFILLED, $p->getState());

        // Resolve with promise
        $v = await($p);

        self::assertEquals('Sakura', $v);
    }

    public function testConstructorCoroutine(): void
    {
        self::useScheduler(new ImmediateScheduler());

        $generator = null;

        $p = new Promise(
            function ($resolve) use (&$generator) {
                $generator = (static function () use ($resolve) {
                    $resolve(yield);
                })();
            }
        );

        $p->then(
            function ($v) {
                $this->values['v1'] = $v;
            }
        );

        $generator->send('Flower');

        $p->wait();

        self::assertEquals('Flower', $this->values['v1']);
    }

    public function testResolve(): void
    {
        [$p, $resolve] = Promise::withResolvers();

        $resolve('Flower');

        self::assertEquals(PromiseState::FULFILLED, $p->getState());
    }

    public function testReject(): void
    {
        [$p,, $reject] = Promise::withResolvers();

        try {
            $reject('Flower');
        } catch (UncaughtException $e) {
            self::assertEquals(PromiseState::REJECTED, $p->getState());
        }
    }

    public function testRejectedWithoutCatch(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Hello');

        $promise = Promise::reject('Hello');
        $promise->wait();
    }

    public function testResolvePendingPromise(): void
    {
        [$promise, $resolve] = Promise::withResolvers();

        $p = Promise::resolve($promise)
            ->then(
                function ($v) {
                    return $v . ' World';
                }
            );

        $resolve('Hello');

        $r = $p->wait();

        self::assertEquals('Hello World', $r);
    }

    public function testAllResolved()
    {
        $p = Promise::all(
            [
                Promise::resolve('A'),
                Promise::resolve('B'),
                Promise::resolve('C'),
            ]
        )
            ->then(
                function ($v) {
                    self::assertEquals(['A', 'B', 'C'], $v);

                    $this->addToAssertionCount(1);
                }
            );

        $p->wait();

        self::assertEquals($this->numberOfAssertionsPerformed(), 1);
    }
    public function testAllWithRejected()
    {
        $p = Promise::all(
            [
                Promise::resolve('A'),
                Promise::reject('B'),
                Promise::resolve('C'),
            ]
        )
            ->catch(
                function ($v) {
                    self::assertEquals('B', $v);

                    $this->addToAssertionCount(1);
                }
            );

        $p->wait();

        self::assertEquals(1, $this->numberOfAssertionsPerformed());
    }

    public function testAllSettled(): void
    {
        self::useScheduler(new DeferredScheduler());

        $promise = Promise::allSettled(
            [
                Promise::resolve('A'),
                Promise::reject('B'),
                Promise::resolve('C'),
            ]
        )
            ->then(
                function (array $results) {
                    /** @var SettledResult[] $results */
                    self::assertEquals(PromiseState::FULFILLED, $results[0]->status);
                    self::assertEquals('A', $results[0]->value);

                    self::assertEquals(PromiseState::REJECTED, $results[1]->status);
                    self::assertEquals('B', $results[1]->value);
                }
            );

        $promise->wait();

        self::useScheduler(new ImmediateScheduler());

        Promise::allSettled(
            [
                Promise::resolve('A'),
                Promise::reject('B'),
                Promise::resolve('C'),
            ]
        )
            ->then(
                function (array $results) {
                    /** @var SettledResult[] $results */
                    self::assertEquals(PromiseState::FULFILLED, $results[0]->status);
                    self::assertEquals('A', $results[0]->value);

                    self::assertEquals(PromiseState::REJECTED, $results[1]->status);
                    self::assertEquals('B', $results[1]->value);
                }
            );
    }

    public function testAny(): void
    {
        $p = Promise::any(
            [
                Promise::resolve('A'),
                Promise::reject('B'),
            ]
        )
            ->then(
                function ($v) {
                    self::assertEquals('A', $v);

                    $this->addToAssertionCount(1);
                }
            );

        $p->wait();

        self::assertEquals(1, $this->numberOfAssertionsPerformed());
    }

    public function testAnyAllRejected(): void
    {
        $p = Promise::any(
            [
                Promise::reject('A'),
                Promise::reject('B'),
            ]
        )
            ->catch(
                function ($e) {
                    self::assertEquals(['A', 'B'], $e);

                    $this->addToAssertionCount(1);
                }
            );

        $p->wait();

        self::assertEquals(1, $this->numberOfAssertionsPerformed());
    }

    public function testTryResolved()
    {
        $reactPromiseFunction = function () {
            $deferred = new \React\Promise\Deferred();

            $promise = $deferred->promise();

            $deferred->resolve('Hello');

            return $promise;
        };

        $p = Promise::try(
            static fn() => $reactPromiseFunction()
        );

        self::assertInstanceOf(Promise::class, $p);

        $p->wait();

        self::assertEquals(PromiseState::FULFILLED, $p->getState());
    }

    public function testTryRejected()
    {
        $reactPromiseFunction = function () {
            $deferred = new \React\Promise\Deferred();

            $promise = $deferred->promise();

            $deferred->reject('Hello');

            return $promise;
        };

        $p = Promise::try(
            static fn() => $reactPromiseFunction()
        );

        $this->expectException(UncaughtException::class);
        $this->expectExceptionMessage('Hello');

        self::assertInstanceOf(Promise::class, $p);

        $p->wait();
    }

    public function testTrySyncThrow()
    {
        $reactPromiseFunction = function () {
            throw new \RuntimeException('Error');
        };

        $p = Promise::try(
            static fn() => $reactPromiseFunction()
        );

        $this->expectException(UncaughtException::class);
        $this->expectExceptionMessage('Error');

        self::assertInstanceOf(Promise::class, $p);

        $p->wait();
    }
}
