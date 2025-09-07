<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Queue\Job\JobController;
use Windwalker\Queue\QueueMessage;
use Windwalker\Queue\Worker;

/**
 * The JobEventTrait class.
 */
trait JobEventTrait
{
    use QueueEventTrait;

    public JobController $controller;

    public Worker $worker {
        get => $this->runner;
    }

    public QueueMessage $message {
        get => $this->controller->message;
    }

    // phpcs:disable
    public object $job {
        get => $this->message->getJob();
    }
}
