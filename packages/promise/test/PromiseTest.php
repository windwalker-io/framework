<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise\Test;

use Exception;
use Windwalker\Promise\Enum\PromiseStatus;
use Windwalker\Promise\Exception\UnsettledException;
use Windwalker\Promise\Promise;
use Windwalker\Promise\Scheduler\DeferredScheduler;
use Windwalker\Promise\Scheduler\ImmediateScheduler;
use Windwalker\Promise\Scheduler\TaskQueue;
use Windwalker\Promise\SettledResult;
use Windwalker\Utilities\Reflection\ReflectAccessor;

use function Windwalker\Promise\await;

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

        try {
            $p->wait();
        } catch (UnsettledException) {
            //
        }

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

        $v = $p->wait();

        self::assertEquals(Promise::FULFILLED, $p->getState());
        self::assertEquals('Flower', $v);

        // Resolve with promise
        $v = await(
            $p = new Promise(
                function ($resolve) {
                    $resolve(
                        new Promise(
                            function ($resolve) {
                                $resolve('Sakura');
                            }
                        )
                    );
                }
            )
        );

        self::assertEquals('Sakura', $v);
    }

    public function testConstructorCoroutine(): void
    {
        self::useScheduler(new ImmediateScheduler());

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

    public function testRejected(): void
    {
        self::markTestIncomplete();
    }

    public function testRejectedWithoutCatch(): void
    {
        static::useScheduler(new DeferredScheduler());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Hello');

        $promise = Promise::rejected('Hello');
        $promise->wait();
    }

    public function testAllResolved()
    {
        $p = Promise::all(
            [
                Promise::resolved('A'),
                Promise::resolved('B'),
                Promise::resolved('C'),
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
                Promise::resolved('A'),
                Promise::rejected('B'),
                Promise::resolved('C'),
            ]
        )
            ->catch(
                function ($v) {
                    self::assertEquals('B', $v);
                }
            );

        $p->wait();

        self::assertCount(2);
    }

    // public function testAllSettled(): void
    // {
    //     self::useScheduler(new DeferredScheduler());
    //
    //     $promise = Promise::allSettled(
    //         [
    //             Promise::resolved('A'),
    //             Promise::rejected('B'),
    //             Promise::resolved('C'),
    //         ]
    //     )
    //         ->then(
    //             function (array $results) {
    //                 /** @var SettledResult[] $results */
    //                 self::assertEquals(PromiseStatus::FULFILLED, $results[0]->status);
    //                 self::assertEquals('A', $results[0]->value);
    //
    //                 self::assertEquals(PromiseStatus::REJECTED, $results[1]->status);
    //                 self::assertEquals('B', $results[1]->value);
    //             }
    //         );
    //
    //     $promise->wait();
    //
    //     self::useScheduler(new ImmediateScheduler());
    //
    //     Promise::allSettled(
    //         [
    //             Promise::resolved('A'),
    //             Promise::rejected('B'),
    //             Promise::resolved('C'),
    //         ]
    //     )
    //         ->then(
    //             function (array $results) {
    //                 /** @var SettledResult[] $results */
    //                 self::assertEquals(PromiseStatus::FULFILLED, $results[0]->status);
    //                 self::assertEquals('A', $results[0]->value);
    //
    //                 self::assertEquals(PromiseStatus::REJECTED, $results[1]->status);
    //                 self::assertEquals('B', $results[1]->value);
    //             }
    //         );
    // }

    // public static function testAny(): void
    // {
    //     $p = Promise::any(
    //         [
    //             Promise::resolved('A'),
    //             Promise::rejected('B'),
    //         ]
    //     )
    //         ->then(
    //             function ($v) {
    //                 self::assertEquals('A', $v);
    //             }
    //         );
    //
    //     $p->wait();
    // }
}
