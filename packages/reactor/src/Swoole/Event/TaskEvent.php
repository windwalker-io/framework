<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Server;
use Windwalker\Event\BaseEvent;
use Windwalker\Http\Server\ServerInterface;

/**
 * The TaskEvent class.
 */
class TaskEvent extends BaseEvent
{
    use ServerEventTrait;

    public function __construct(
        Server $swooleServer,
        ServerInterface $server,
        public int $taskId,
        public int $srcWorkerId,
        public mixed $data,
    ) {
        $this->swooleServer = $swooleServer;
        $this->server = $server;
    }
}
