<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Event\BaseEvent;
use Windwalker\Event\Events\ErrorEventTrait;
use Windwalker\Queue\Job\JobWrapperInterface;
use Windwalker\Queue\Queue;
use Windwalker\Queue\QueueMessage;
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
        QueueMessage $message,
        Worker $worker,
        Queue $queue,
        public int|false $backoff = false,
        public bool $maxAttemptsExceeds = false,
        public bool $abandoned = false,
    ) {
        $this->message = $message;
        $this->worker = $worker;
        $this->queue = $queue;
        $this->exception = $exception;
    }
}
