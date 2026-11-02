<?php

declare(strict_types=1);

namespace Windwalker\Promise\Test\Scheduler;

use DomainException;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Promise\Scheduler\DeferredScheduler;
use Windwalker\Promise\Scheduler\ScheduleCursor;
use Windwalker\Promise\Scheduler\ScheduleRunner;
use Windwalker\Promise\Scheduler\SchedulerInterface;

class ScheduleRunnerTest extends TestCase
{
    private static function unsupportedScheduler(): SchedulerInterface
    {
        return new class () implements SchedulerInterface {
            public static function isSupported(): bool
            {
                return false;
            }

            public function createCursor(): ScheduleCursor
            {
                throw new LogicException('Should not be used.');
            }

            public function schedule(ScheduleCursor $cursor, callable $callback): void
            {
                throw new LogicException('Should not be used.');
            }

            public function wait(ScheduleCursor $cursor): void
            {
                throw new LogicException('Should not be used.');
            }

            public function done(?ScheduleCursor $cursor): void
            {
                throw new LogicException('Should not be used.');
            }
        };
    }

    #[Test]
    public function getAvailableSchedulerSkipsUnsupportedSchedulers(): void
    {
        $supported = new DeferredScheduler();
        $runner = new ScheduleRunner([static::unsupportedScheduler(), $supported]);

        self::assertSame($supported, $runner->getAvailableScheduler());
    }

    #[Test]
    public function getAvailableSchedulerThrowsWhenNoSchedulerIsSupported(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageIsOrContains('No available async handlers');

        new ScheduleRunner([static::unsupportedScheduler()])->getAvailableScheduler();
    }

    #[Test]
    public function getSchedulersFallsBackToDefaultsWhenEmpty(): void
    {
        $schedulers = new ScheduleRunner()->getSchedulers();

        self::assertNotEmpty($schedulers);
    }

    #[Test]
    public function setSchedulersReplacesSchedulers(): void
    {
        $scheduler = new DeferredScheduler();
        $runner = new ScheduleRunner();

        self::assertSame($runner, $runner->setSchedulers([$scheduler]));
        self::assertSame([$scheduler], $runner->getSchedulers());
    }

    #[Test]
    public function scheduleWaitAndDoneDelegateToAvailableScheduler(): void
    {
        $runner = new ScheduleRunner([new DeferredScheduler()]);
        $cursor = $runner->createCursor();
        $calls = [];

        $runner->schedule($cursor, function () use (&$calls) {
            $calls[] = 'scheduled';
        });

        self::assertSame([], $calls);

        $runner->wait($cursor);
        $runner->done($cursor);

        self::assertSame(['scheduled'], $calls);
    }
}
