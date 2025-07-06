<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Queue\Job\JobController;
use Windwalker\Queue\QueueMessage;

/**
 * The JobEventTrait class.
 */
trait JobEventTrait
{
    use QueueEventTrait;

    public JobController $controller;

    public QueueMessage $message {
        get => $this->controller->message;
    }

    // phpcs:disable
    public object $job {
        get => $this->message->getJob();
    }
}
