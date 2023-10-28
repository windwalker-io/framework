<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

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
