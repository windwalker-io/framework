<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Server;
use Windwalker\Event\BaseEvent;
use Windwalker\Http\Server\ServerInterface;
use Windwalker\Reactor\WebSocket\WebSocketFrameInterface;
use Windwalker\Reactor\WebSocket\WebSocketRequestInterface;
use Windwalker\WebSocket\Application\WsApplicationInterface;

/**
 * The MessageEvent class.
 */
class MessageEvent extends BaseEvent
{
    use ServerEventTrait;

    public int $fd {
        get => $this->frame->getFd();
    }

    public int $data {
        get => $this->frame->getData();
    }

    public function __construct(
        Server $swooleServer,
        ServerInterface $server,
        public WebSocketFrameInterface $frame,
    ) {
        $this->swooleServer = $swooleServer;
        $this->server = $server;
    }

    public function getRequestFromMemory(WsApplicationInterface $app): WebSocketRequestInterface
    {
        return $app->getRememberedRequest($this->fd)
            ->withFrame($this->frame)
            ->withMethod('MESSAGE');
    }
}
