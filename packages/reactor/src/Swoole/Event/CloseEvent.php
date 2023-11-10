<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;
use Windwalker\Reactor\WebSocket\WebSocketFrame;
use Windwalker\Reactor\WebSocket\WebSocketFrameInterface;
use Windwalker\Reactor\WebSocket\WebSocketRequestInterface;
use Windwalker\WebSocket\Application\WsApplicationInterface;

/**
 * The CloseEvent class.
 */
class CloseEvent extends AbstractEvent
{
    use ServerEventTrait;
    use TcpEventTrait;

    public function createWebSocketFrame(): WebSocketFrameInterface
    {
        return new WebSocketFrame($this->getFd());
    }

    public function getRequestFromMemory(WsApplicationInterface $app): WebSocketRequestInterface
    {
        return $app->getRememberedRequest($this->getFd())
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
