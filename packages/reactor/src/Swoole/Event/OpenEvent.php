<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Http\Request;
use Swoole\Server;
use Windwalker\Event\BaseEvent;
use Windwalker\Http\Server\ServerInterface;
use Windwalker\Reactor\WebSocket\WebSocketRequest;

/**
 * The OpenEvent class.
 */
class OpenEvent extends BaseEvent
{
    use ServerEventTrait;

    public function __construct(
        public WebSocketRequest $request,
        public Request $swooleRequest,
        Server $swooleServer,
        ServerInterface $server,
    ) {
        $this->swooleServer = $swooleServer;
        $this->server = $server;
    }
}
