<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The WorkerExitEvent class.
 */
class WorkerExitEvent extends AbstractEvent
{
    use ServerEventTrait;
    use WorkerEventTrait;
}
