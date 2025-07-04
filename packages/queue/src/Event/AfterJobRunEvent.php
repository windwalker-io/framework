<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Event\BaseEvent;
use Windwalker\Queue\Job\JobController;
use Windwalker\Queue\Job\JobWrapperInterface;
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
        public JobController $controller,
        QueueMessage $message,
        Worker $worker,
        Queue $queue,
    ) {
        $this->message = $message;
        $this->worker = $worker;
        $this->queue = $queue;
    }
}
