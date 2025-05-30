<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Server;
use Windwalker\Event\BaseEvent;
use Windwalker\Http\Server\ServerInterface;

/**
 * The FinishEvent class.
 */
class FinishEvent extends BaseEvent
{
    use ServerEventTrait;

    /**
     * @param  Server           $swooleServer
     * @param  ServerInterface  $server
     * @param  int              $taskId
     * @param  mixed|null       $data
     */
    public function __construct(
        Server $swooleServer,
        ServerInterface $server,
        public mixed $data,
        public int $taskId = 0,
    ) {
        $this->swooleServer = $swooleServer;
        $this->server = $server;
    }
}
