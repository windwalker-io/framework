<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise\Test;

use PHPUnit\Framework\TestCase;
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
    protected $values = '';

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        TaskQueue::getInstance()->disableShutdownRunner();

        self::useScheduler(new ImmediateScheduler());
    }

    protected function setUp(): void
    {
        $this->values = [];
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
