<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The ManagerStopEvent class.
 */
class ManagerStopEvent extends AbstractEvent
{
    use ServerEventTrait;
}
