<?php

declare(strict_types=1);

namespace Windwalker\Promise\Test;

use PHPUnit\Framework\Attributes\Test;
use React\EventLoop\StreamSelectLoop;
use Swoole\Event;
use Windwalker\Promise\Promise;
use Windwalker\Promise\Scheduler\DeferredScheduler;
use Windwalker\Promise\Scheduler\EventLoopScheduler;
use Windwalker\Promise\Scheduler\ScheduleRunner;
use Windwalker\Promise\Scheduler\SwooleScheduler;
use Windwalker\Promise\Scheduler\TaskQueue;
use Windwalker\Test\Traits\Reactor\SwooleTestTrait;

use function Co\run;

/**
 * The AsyncPromiseTest class.
 */
class AsyncPromiseTest extends AbstractPromiseTestCase
{
    use SwooleTestTrait;

    protected static function prepareDefaultScheduler(): void
    {
        self::useScheduler(new DeferredScheduler());
    }

    protected function flushAsyncEvents(): void
    {
        // Ensure async events ran
        $this->nextTick();
    }

    /**
     * @see  AsyncPromise
     */
    #[Test]
    public function constructorResolvesHandlersAsynchronously(): void
    {
        $promise = new Promise(
            function ($resolve) {
                $resolve('Hello');
            }
        );

        $promise->then(
            function ($v) {
                $this->values['v1'] = $v;
            }
        );

        self::assertArrayNotHasKey('v1', $this->values);

        TaskQueue::getInstance()->run();

        self::assertEquals('Hello', $this->values['v1']);
    }

    #[Test]
    public function constructorResolvingPromiseAdoptsItsValue(): void
    {
        $promise = new Promise(
            function ($resolve) {
                $resolve(
                    new Promise(
                        function ($re, $rj) {
                            $re('Flower');
                        }
                    )
                );
            }
        );

        $promise->then(
            function ($v) {
                $this->values['v1'] = $v;
            }
        );

        self::assertArrayNotHasKey('v1', $this->values);

        TaskQueue::getInstance()->run();

        self::assertEquals('Flower', $this->values['v1']);
    }

    #[Test]
    public function thenReturningPromiseChainsResolvedValue(): void
    {
        $promise = new Promise(
            function ($resolve) {
                $resolve(
                    new Promise(
                        function ($re, $rj) {
                            $re('Flower');
                        }
                    )
                );
            }
        );

        $promise->then(
            function ($v) {
                return new Promise(
                    function ($re) {
                        $re('YOO');
                    }
                );
            }
        )
            ->then(
                function ($v) {
                    $this->values['v1'] = $v;
                }
            );

        self::assertArrayNotHasKey('v1', $this->values);

        TaskQueue::getInstance()->run();

        self::assertEquals('YOO', $this->values['v1']);
    }

    #[Test]
    public function thenHandlerExceptionIsThrownWhenQueueRuns(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageIsOrContains('Test Error');

        $p = Promise::resolve('123')
            ->then(
                fn() => throw new \Exception('Test Error')
            );

        TaskQueue::getInstance()->run();
    }

    #[Test]
    public function swooleSchedulerWaitsForChainedPromises(): void
    {
        static::skipIfSwooleNotInstalled();

        ScheduleRunner::getInstance()->setSchedulers(
            [
                new SwooleScheduler(),
            ]
        );

        run(
            function () {
                $p = new Promise(
                    function ($resolve) {
                        $resolve(
                            new Promise(
                                function ($re, $rj) {
                                    $re('Flower');
                                }
                            )
                        );
                    }
                );

                $p = $p->then(
                    function ($v) {
                        return new Promise(
                            function ($re) {
                                $re('YOO');
                            }
                        );
                    }
                )
                    ->then(
                        function ($v) {
                            $this->values['v1'] = $v;

                            return 'GOO';
                        }
                    );

                self::assertArrayNotHasKey('v1', $this->values);

                $value = $p->wait();

                self::assertEquals('YOO', $this->values['v1']);
                self::assertEquals('GOO', $value);

                $this->addToAssertionCount(3);
            }
        );

        self::assertEquals(3, $this->numberOfAssertionsPerformed());
    }

    #[Test]
    public function deferredSchedulerRunsHandlersFromPeriodicLoopTimer(): void
    {
        self::useScheduler(new DeferredScheduler());

        $loop = new StreamSelectLoop();

        $p = new Promise(
            static function (callable $resolve) {
                $resolve('Hello');
            }
        );
        $p->then(
            function ($v) use ($loop) {
                $this->values['v1'] = $v;

                $loop->stop();
            }
        );

        $loop->addPeriodicTimer(0, [TaskQueue::getInstance(), 'run']);
        $loop->run();

        self::assertEquals('Hello', $this->values['v1']);
    }

    #[Test]
    public function reactEventLoopSchedulerRunsHandlers(): void
    {
        $loop = new StreamSelectLoop();

        self::useScheduler(new EventLoopScheduler($loop));

        $p = new Promise(
            static function (callable $resolve) {
                $resolve('Hello');
            }
        );
        $p->then(
            function ($v) use ($loop) {
                $this->values['v1'] = $v;

                $loop->stop();
            }
        );

        $loop->run();

        self::assertEquals('Hello', $this->values['v1']);
    }

    #[Test]
    public function swooleEventLoopSchedulerWaitReturnsResolvedValue(): void
    {
        static::skipIfSwooleNotInstalled();

        self::useScheduler(EventLoopScheduler::swoole());

        go(
            function () {
                $p = new Promise(
                    static function (callable $resolve) {
                        $resolve('Hello');
                    }
                );
                $p = $p->then(
                    function ($v) {
                        $this->values['v1'] = $v;

                        return $v;
                    }
                );

                $v = $p->wait();

                self::assertEquals('Hello', $v);
                self::assertEquals('Hello', $this->values['v1']);
            }
        );
    }

    #[Test]
    public function swooleEventLoopSchedulerRunsHandlerAfterDeferredResolve(): void
    {
        static::skipIfSwooleNotInstalled();

        self::useScheduler(EventLoopScheduler::swoole());

        go(
            function () {
                $p = new Promise(
                    static function (callable $resolve) {
                        \Windwalker\go(
                            function () use ($resolve) {
                                $resolve('Hello');
                            }
                        );
                    }
                );
                $p->then(
                    function ($v) {
                        $this->values['v1'] = $v;
                    }
                );
            }
        );

        Event::dispatch();

        self::assertEquals('Hello', $this->values['v1']);
    }
}
