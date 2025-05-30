<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Server;
use Windwalker\Event\BaseEvent;
use Windwalker\Http\Server\ServerInterface;

/**
 * The PacketEvent class.
 */
class PacketEvent extends BaseEvent
{
    use ServerEventTrait;

    public function __construct(
        Server $swooleServer,
        ServerInterface $server,
        public string $data,
        public array $clientInfo,
    ) {
        $this->swooleServer = $swooleServer;
        $this->server = $server;
    }
}
