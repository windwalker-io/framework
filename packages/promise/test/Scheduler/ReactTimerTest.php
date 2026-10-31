<?php

declare(strict_types=1);

namespace Windwalker\Promise\Test\Scheduler;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\EventLoop\LoopInterface;
use Windwalker\Promise\Scheduler\EventLoopScheduler;

class ReactTimerTest extends TestCase
{
    #[Test]
    public function createReactTimerReturnsScheduleWaiterAndDoneCallables(): void
    {
        $loop = $this->createMock(LoopInterface::class);
        $callback = static fn() => null;

        $loop->expects($this->once())
            ->method('addTimer')
            ->with(0.001, $callback);

        $timer = EventLoopScheduler::createReactTimer($loop);
        [$schedule, $waiter, $done] = $timer();

        $schedule($callback);

        self::assertNull($waiter());
        self::assertNull($done());
    }

    #[Test]
    public function schedulerConstructedWithLoopSchedulesThroughLoopTimer(): void
    {
        $loop = $this->createMock(LoopInterface::class);
        $callback = static fn() => null;

        $loop->expects($this->once())
            ->method('addTimer')
            ->with(0.001, $callback);

        $scheduler = new EventLoopScheduler($loop);
        $cursor = $scheduler->createCursor();

        $scheduler->schedule($cursor, $callback);
        $scheduler->wait($cursor);
        $scheduler->done($cursor);
    }

    #[Test]
    public function createSwooleTimerReturnsCallableWhenSwooleIsInstalled(): void
    {
        if (!extension_loaded('swoole')) {
            $this->markTestSkipped('Swoole is not installed.');
        }

        self::assertIsCallable(EventLoopScheduler::createSwooleTimer(1));
    }
}
