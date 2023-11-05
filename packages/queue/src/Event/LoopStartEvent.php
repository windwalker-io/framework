<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The WorkerLoopStartEvent class.
 */
class LoopStartEvent extends AbstractEvent
{
    use QueueEventTrait;
}
