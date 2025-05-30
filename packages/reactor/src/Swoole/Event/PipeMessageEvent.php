<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Server;
use Windwalker\Event\BaseEvent;
use Windwalker\Http\Server\ServerInterface;

/**
 * The PipeMessageEvent.php class.
 */
class PipeMessageEvent extends BaseEvent
{
    use ServerEventTrait;

    public function __construct(
        Server $swooleServer,
        ServerInterface $server,
        public int $srcWorkerId,
        public mixed $message,
    ) {
        $this->swooleServer = $swooleServer;
        $this->server = $server;
    }
}
