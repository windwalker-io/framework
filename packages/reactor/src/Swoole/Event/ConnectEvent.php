<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Server;
use Windwalker\Event\BaseEvent;
use Windwalker\Http\Server\ServerInterface;

/**
 * The ConnectEvent class.
 */
class ConnectEvent extends BaseEvent
{
    use ServerEventTrait;
    use TcpEventTrait;

    public function __construct(
        Server $swooleServer,
        ServerInterface $server,
        int $reactorId,
        int $fd = 0,
    ) {
        $this->swooleServer = $swooleServer;
        $this->server = $server;
        $this->fd = $fd;
        $this->reactorId = $reactorId;
    }
}
