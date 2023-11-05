<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The ShutdownEvent class.
 */
class ShutdownEvent extends AbstractEvent
{
    use ServerEventTrait;
}
