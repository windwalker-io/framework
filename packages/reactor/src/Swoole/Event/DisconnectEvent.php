<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Server;
use Windwalker\Event\BaseEvent;
use Windwalker\Http\Server\ServerInterface;

/**
 * The DisconnectEvent class.
 */
class DisconnectEvent extends BaseEvent
{
    use ServerEventTrait;

    public function __construct(
        Server $swooleServer,
        ServerInterface $server,
        public int $fd,
    ) {
        $this->swooleServer = $swooleServer;
        $this->server = $server;
    }
}
