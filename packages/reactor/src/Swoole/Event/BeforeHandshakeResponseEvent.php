<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Server;
use Windwalker\Event\BaseEvent;
use Windwalker\Http\Server\ServerInterface;

/**
 * The BeforeHandshakeResponseEvent class.
 */
class BeforeHandshakeResponseEvent extends BaseEvent
{
    use ServerEventTrait;

    public function __construct(
        public Request $request,
        public Response $response,
        Server $swooleServer,
        ServerInterface $server,
    ) {
        $this->swooleServer = $swooleServer;
        $this->server = $server;
    }
}
