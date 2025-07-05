<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Queue\Job\JobWrapperInterface;
use Windwalker\Queue\QueueMessage;
use Windwalker\Utilities\Assert\TypeAssert;

/**
 * The JobEventTrait class.
 */
trait JobEventTrait
{
    use QueueEventTrait;

    public QueueMessage $message;

    // phpcs:disable
    public object $job {
        get => $this->message->getJob();
    }
}
