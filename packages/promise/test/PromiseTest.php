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
use Windwalker\Promise\Promise;
use Windwalker\Promise\Scheduler\DeferredScheduler;
use Windwalker\Promise\Scheduler\ImmediateScheduler;
use Windwalker\Promise\SettledResult;
use Windwalker\Utilities\Reflection\ReflectAccessor;

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

        self::assertEquals(Promise::FULFILLED, ReflectAccessor::getValue($p, 'state'));
        self::assertEquals('Flower', ReflectAccessor::getValue($p, 'value'));

        // Resolve with promise
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
        );

        self::assertEquals('Sakura', ReflectAccessor::getValue($p, 'value'));
    }

    public function testConstructorCoroutine(): void
    {
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

        self::assertEquals('Flower', $this->values['v1']);
    }

    public function testRejected(): void
    {
        self::markTestIncomplete();
    }

    public function testRejectedWithoutCatch(): void
    {
        self::markTestSkipped('Enable this after async promise prepared');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Hello');

        Promise::rejected('Hello');
    }

    public function testAll()
    {
        Promise::all(
            [
                Promise::resolved('A'),
                Promise::resolved('B'),
                Promise::resolved('C'),
            ]
        )
            ->then(
                function ($v) {
                    self::assertEquals(['A', 'B', 'C'], $v);
                }
            );

        Promise::all(
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
    }

    public function testAllSettled(): void
    {
        self::useScheduler(new DeferredScheduler());

        $promise = Promise::allSettled(
            [
                Promise::resolved('A'),
                Promise::rejected('B'),
                Promise::resolved('C'),
            ]
        )
            ->then(
                function (array $results) {
                    /** @var SettledResult[] $results */
                    self::assertEquals(PromiseStatus::FULFILLED, $results[0]->status);
                    self::assertEquals('A', $results[0]->value);

                    self::assertEquals(PromiseStatus::REJECTED, $results[1]->status);
                    self::assertEquals('B', $results[1]->value);
                }
            );

        $promise->wait();

        self::useScheduler(new ImmediateScheduler());

        Promise::allSettled(
            [
                Promise::resolved('A'),
                Promise::rejected('B'),
                Promise::resolved('C'),
            ]
        )
            ->then(
                function (array $results) {
                    /** @var SettledResult[] $results */
                    self::assertEquals(PromiseStatus::FULFILLED, $results[0]->status);
                    self::assertEquals('A', $results[0]->value);

                    self::assertEquals(PromiseStatus::REJECTED, $results[1]->status);
                    self::assertEquals('B', $results[1]->value);
                }
            );
    }
}
