<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The ReceiveEvent class.
 */
class ReceiveEvent extends AbstractEvent
{
    use ServerEventTrait;
    use TcpEventTrait;
}
