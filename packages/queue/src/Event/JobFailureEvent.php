<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Event\BaseEvent;
use Windwalker\Event\Events\ErrorEventTrait;
use Windwalker\Queue\Job\JobController;
use Windwalker\Queue\Queue;
use Windwalker\Queue\Worker;

/**
 * The JobFailure class.
 */
class JobFailureEvent extends BaseEvent
{
    use JobEventTrait;
    use ErrorEventTrait;

    public function __construct(
        \Throwable $exception,
        JobController $controller,
        Worker $runner,
        Queue $queue,
        public int|false $backoff = false,
        public bool $maxAttemptsExceeds = false,
    ) {
        $this->exception = $exception;
        $this->controller = $controller;
        $this->runner = $runner;
        $this->queue = $queue;
    }
}
