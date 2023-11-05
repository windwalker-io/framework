<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The ManagerStartEvent class.
 */
class ManagerStartEvent extends AbstractEvent
{
    use ServerEventTrait;
}
