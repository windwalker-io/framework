<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Server;
use Windwalker\Event\BaseEvent;
use Windwalker\Http\Server\ServerInterface;

/**
 * The WorkerStopEvent class.
 */
class WorkerStopEvent extends BaseEvent
{
    use ServerEventTrait;
    use WorkerEventTrait;

    public function __construct(
        Server $swooleServer,
        ServerInterface $server,
        int $workerId,
    ) {
        $this->swooleServer = $swooleServer;
        $this->server = $server;
        $this->workerId = $workerId;
    }
}
