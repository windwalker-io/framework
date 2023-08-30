<?php

/**
 * Part of cati project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;
use Windwalker\Reactor\WebSocket\WebSocketFrame;
use Windwalker\Reactor\WebSocket\WebSocketFrameInterface;

/**
 * The CloseEvent class.
 */
class CloseEvent extends AbstractEvent
{
    use ServerEventTrait;
    use TcpEventTrait;

    public function createWocketFrame(): WebSocketFrameInterface
    {
        return new WebSocketFrame($this->getFd());
    }
}
