<?php

declare(strict_types=1);

namespace Windwalker\Promise\Test\Scheduler;

use DomainException;
use PHPUnit\Framework\Attributes\Test;
use React\EventLoop\StreamSelectLoop;
use Windwalker\Promise\Scheduler\DeferredScheduler;
use Windwalker\Promise\Scheduler\EventLoopScheduler;
use Windwalker\Promise\Scheduler\ImmediateScheduler;
use Windwalker\Promise\Scheduler\TaskQueue;
use Windwalker\Promise\Test\AbstractPromiseTestCase;

class SchedulerTest extends AbstractPromiseTestCase
{
    #[Test]
    public function immediateSchedulerRunsCallbackAtOnce(): void
    {
        $scheduler = new ImmediateScheduler();
        $ran = false;

        $scheduler->schedule($scheduler->createCursor(), function () use (&$ran) {
            $ran = true;
        });

        self::assertTrue(ImmediateScheduler::isSupported());
        self::assertTrue($ran);
    }

    #[Test]
    public function deferredSchedulerQueuesCallbackUntilWait(): void
    {
        $scheduler = new DeferredScheduler();
        $cursor = $scheduler->createCursor();
        $ran = false;

        $scheduler->schedule($cursor, function () use (&$ran) {
            $ran = true;
        });

        self::assertTrue(DeferredScheduler::isSupported());
        self::assertFalse($ran);

        $scheduler->wait($cursor);

        self::assertTrue($ran);
    }

    #[Test]
    public function registerEventLoopCallsCallable(): void
    {
        $called = 0;

        $scheduler = DeferredScheduler::registerEventLoop(
            function () use (&$called) {
                $called++;
            },
            true
        );

        self::assertSame(1, $called);
        self::assertInstanceOf(DeferredScheduler::class, $scheduler);
    }

    #[Test]
    public function registerEventLoopAddsPeriodicTimerThatRunsTaskQueue(): void
    {
        $loop = new StreamSelectLoop();
        $ran = false;

        DeferredScheduler::registerEventLoop($loop, true);

        TaskQueue::getInstance()->push(function () use ($loop, &$ran) {
            $ran = true;
            $loop->stop();
        });

        $loop->run();

        self::assertTrue($ran);
    }

    #[Test]
    public function eventLoopSchedulerUsesCustomTimerCallbackTriplet(): void
    {
        $log = [];

        $scheduler = new EventLoopScheduler(
            function () use (&$log) {
                return [
                    function (callable $cb) use (&$log) {
                        $log[] = 'schedule';
                        $cb();
                    },
                    function () use (&$log) {
                        $log[] = 'wait';
                    },
                    function () use (&$log) {
                        $log[] = 'done';
                    },
                ];
            }
        );

        $cursor = $scheduler->createCursor();
        $scheduler->schedule($cursor, function () use (&$log) {
            $log[] = 'callback';
        });
        $scheduler->wait($cursor);
        $scheduler->done($cursor);
        $scheduler->done(null);

        self::assertTrue(EventLoopScheduler::isSupported());
        self::assertSame(['schedule', 'callback', 'wait', 'done'], $log);
    }

    #[Test]
    public function createSwooleTimerThrowsWhenSwooleIsNotInstalled(): void
    {
        if (extension_loaded('swoole')) {
            $this->markTestSkipped('Swoole is installed.');
        }

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageIsOrContains('Swoole not installed');

        EventLoopScheduler::createSwooleTimer();
    }
}
