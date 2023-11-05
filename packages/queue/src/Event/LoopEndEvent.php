<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The LoopEndEvent class.
 */
class LoopEndEvent extends AbstractEvent
{
    use QueueEventTrait;
}
