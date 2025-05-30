<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Event\BaseEvent;
use Windwalker\Queue\Queue;
use Windwalker\Queue\QueueMessage;
use Windwalker\Queue\Worker;

/**
 * The AfterJobRunEvent class.
 */
class AfterJobRunEvent extends BaseEvent
{
    use JobEventTrait;

    public function __construct(
        QueueMessage $message,
        callable $job,
        Worker $worker,
        Queue $queue,
    ) {
        $this->message = $message;
        $this->job = $job;
        $this->worker = $worker;
        $this->queue = $queue;
    }
}
