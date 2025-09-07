<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Event\BaseEvent;
use Windwalker\Queue\AbstractRunner;
use Windwalker\Queue\Queue;
use Windwalker\Queue\Worker;

/**
 * The StopEvent class.
 */
class StopEvent extends BaseEvent
{
    use QueueEventTrait;

    public function __construct(
        public string $reason,
        AbstractRunner $runner,
        Queue $queue,
    ) {
        $this->runner = $runner;
        $this->queue = $queue;
    }
}
