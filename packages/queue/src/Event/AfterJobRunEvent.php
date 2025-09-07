<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Event\BaseEvent;
use Windwalker\Queue\Job\JobController;
use Windwalker\Queue\Queue;
use Windwalker\Queue\Worker;

/**
 * The AfterJobRunEvent class.
 */
class AfterJobRunEvent extends BaseEvent
{
    use JobEventTrait;

    public function __construct(
        JobController $controller,
        Worker $runner,
        Queue $queue,
    ) {
        $this->runner = $runner;
        $this->queue = $queue;
        $this->controller = $controller;
    }
}
