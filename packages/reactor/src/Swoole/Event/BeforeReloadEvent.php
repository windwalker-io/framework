<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Server;
use Windwalker\Event\BaseEvent;
use Windwalker\Http\Server\ServerInterface;

/**
 * The BeforeReloadEvent class.
 */
class BeforeReloadEvent extends BaseEvent
{
    use ServerEventTrait;

    public function __construct(
        Server $swooleServer,
        ServerInterface $server,
    ) {
        $this->swooleServer = $swooleServer;
        $this->server = $server;
    }
}
