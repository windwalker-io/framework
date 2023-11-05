<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The WorkerStartEvent class.
 */
class WorkerStartEvent extends AbstractEvent
{
    use ServerEventTrait;
    use WorkerEventTrait;
}
