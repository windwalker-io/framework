<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Server;
use Windwalker\Http\Server\ServerInterface;
use Windwalker\Utilities\Accessible\AccessorBCTrait;

/**
 * Trait ServerEventTrait
 */
trait ServerEventTrait
{
    use AccessorBCTrait;

    public Server $swooleServer;

    public ServerInterface $server;
}
