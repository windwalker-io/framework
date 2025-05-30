<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Queue\Queue;
use Windwalker\Queue\Worker;
use Windwalker\Utilities\Accessible\AccessorBCTrait;

/**
 * Trait QueueEventTrait
 */
trait QueueEventTrait
{
    use AccessorBCTrait;

    public Worker $worker;

    public Queue $queue;
}
