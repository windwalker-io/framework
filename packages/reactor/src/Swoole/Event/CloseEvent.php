<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Server;
use Windwalker\Event\BaseEvent;
use Windwalker\Http\Server\ServerInterface;
use Windwalker\Reactor\WebSocket\WebSocketFrame;
use Windwalker\Reactor\WebSocket\WebSocketFrameInterface;
use Windwalker\Reactor\WebSocket\WebSocketRequestInterface;
use Windwalker\WebSocket\Application\WsApplicationInterface;

/**
 * The CloseEvent class.
 */
class CloseEvent extends BaseEvent
{
    use ServerEventTrait;
    use TcpEventTrait;

    public function __construct(
        Server $swooleServer,
        ServerInterface $server,
        int $fd,
        int $reactorId,
    ) {
        $this->swooleServer = $swooleServer;
        $this->server = $server;
        $this->fd = $fd;
        $this->reactorId = $reactorId;
    }

    public function createWebSocketFrame(): WebSocketFrameInterface
    {
        return new WebSocketFrame($this->fd);
    }

    public function getRequestFromMemory(WsApplicationInterface $app): WebSocketRequestInterface
    {
        return $app->getRememberedRequest($this->fd)
            ->withFrame($this->createWebSocketFrame())
            ->withMethod('CLOSE');
    }

    public function getAndForgetRequest(WsApplicationInterface $app): WebSocketRequestInterface
    {
        $request = $this->getRequestFromMemory($app);

        $app->forgetRequest($request->getFd());

        return $request;
    }
}
