<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Server;
use Windwalker\Event\BaseEvent;
use Windwalker\Http\Server\ServerInterface;

/**
 * The ReceiveEvent class.
 */
class ReceiveEvent extends BaseEvent
{
    use ServerEventTrait;
    use TcpEventTrait;

    public function __construct(
        Server $swooleServer,
        ServerInterface $server,
        public string $data,
        int $reactorId,
        int $fd = 0,
    ) {
        $this->swooleServer = $swooleServer;
        $this->server = $server;
        $this->fd = $fd;
        $this->reactorId = $reactorId;
    }
}
