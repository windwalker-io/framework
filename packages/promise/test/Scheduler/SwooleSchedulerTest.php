<?php

declare(strict_types=1);

namespace Windwalker\Promise\Test\Scheduler;

use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Promise\Scheduler\EventLoopScheduler;
use Windwalker\Promise\Scheduler\ScheduleCursor;
use Windwalker\Promise\Scheduler\SwooleScheduler;

class SwooleSchedulerTest extends TestCase
{
    #[Test]
    public function isNotSupportedWithoutSwooleCoroutineContext(): void
    {
        if (extension_loaded('swoole')) {
            $this->markTestSkipped('Outside of a coroutine only when Swoole is not running one.');
        }

        self::assertFalse(SwooleScheduler::isSupported());
    }

    #[Test]
    public function waitRejectsCursorThatIsNotAChannel(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains('Cursor should be');

        (new SwooleScheduler())->wait(new ScheduleCursor('not a channel'));
    }

    #[Test]
    public function doneRejectsCursorThatIsNotAChannel(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains('got string');

        (new SwooleScheduler())->done(new ScheduleCursor('not a channel'));
    }

    #[Test]
    public function doneWithoutCursorDoesNothing(): void
    {
        (new SwooleScheduler(5))->done(null);

        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function createCursorWrapsChannelWhenSwooleIsInstalled(): void
    {
        if (!extension_loaded('swoole')) {
            $this->markTestSkipped('Swoole is not installed.');
        }

        $cursor = (new SwooleScheduler())->createCursor();

        self::assertInstanceOf(\Swoole\Coroutine\Channel::class, $cursor->get());
    }

    #[Test]
    public function eventLoopSwooleFactoryThrowsWhenSwooleIsNotInstalled(): void
    {
        if (extension_loaded('swoole')) {
            self::assertInstanceOf(EventLoopScheduler::class, EventLoopScheduler::swoole(3));

            return;
        }

        $this->expectException(\DomainException::class);

        EventLoopScheduler::swoole();
    }
}
