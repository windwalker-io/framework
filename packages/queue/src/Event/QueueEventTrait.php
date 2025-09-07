<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Queue\AbstractRunner;
use Windwalker\Queue\Queue;
use Windwalker\Utilities\Accessible\AccessorBCTrait;

/**
 * Trait QueueEventTrait
 */
trait QueueEventTrait
{
    use AccessorBCTrait;

    public AbstractRunner $runner;

    public Queue $queue;
}
