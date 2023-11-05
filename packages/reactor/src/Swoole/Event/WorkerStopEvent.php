<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The WorkerStopEvent class.
 */
class WorkerStopEvent extends AbstractEvent
{
    use ServerEventTrait;
    use WorkerEventTrait;
}
