<?php

declare(strict_types=1);

namespace Windwalker\Promise\Test;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\TestCase;
use Windwalker\Promise\Scheduler\DeferredScheduler;
use Windwalker\Promise\Scheduler\ImmediateScheduler;
use Windwalker\Promise\Scheduler\SchedulerInterface;
use Windwalker\Promise\Scheduler\ScheduleRunner;
use Windwalker\Promise\Scheduler\TaskQueue;

/**
 * The PromiseTestTrait class.
 */
abstract class AbstractPromiseTestCase extends TestCase
{
    /**
     * @var array
     */
    protected array $values = [];

    protected array $serial = [];

    #[BeforeClass]
    public static function disableShutdownRunner(): void
    {
        TaskQueue::getInstance()->disableShutdownRunner();
    }

    #[Before]
    protected function resetValuesAndScheduler(): void
    {
        $this->values = [];
        $this->serial = [];

        // Reset every test
        static::prepareDefaultScheduler();
    }

    #[After]
    protected function clearTaskQueue(): void
    {
        TaskQueue::getInstance()->clear();

        $this->flushAsyncEvents();
    }

    /**
     * Hook for subclasses, runs after the task queue is cleared.
     */
    protected function flushAsyncEvents(): void
    {
    }

    protected static function prepareDefaultScheduler(): void
    {
        static::useScheduler(new DeferredScheduler());
    }

    /**
     * useHandler
     *
     * @param  SchedulerInterface  $handler
     *
     * @return  void
     */
    protected static function useScheduler(SchedulerInterface $handler): void
    {
        ScheduleRunner::getInstance()->setSchedulers([$handler]);
    }
}
