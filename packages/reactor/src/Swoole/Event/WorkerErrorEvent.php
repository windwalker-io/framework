<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Server;
use Windwalker\Event\BaseEvent;
use Windwalker\Http\Server\ServerInterface;

/**
 * The StartEvent class.
 */
class WorkerErrorEvent extends BaseEvent
{
    use ServerEventTrait;

    public function __construct(
        Server $swooleServer,
        ServerInterface $server,
        public int $workerId,
        public int $workerPid,
        public int $exitCode,
        public int $signal,
    ) {
        $this->swooleServer = $swooleServer;
        $this->server = $server;
    }
}
