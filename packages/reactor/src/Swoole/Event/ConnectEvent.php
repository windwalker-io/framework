<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The ConnectEvent class.
 */
class ConnectEvent extends AbstractEvent
{
    use ServerEventTrait;
    use TcpEventTrait;
}
