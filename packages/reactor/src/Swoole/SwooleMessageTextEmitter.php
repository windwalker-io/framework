<?php

/**
 * Part of cati project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole;

use Swoole\WebSocket\Server;
use Windwalker\Reactor\WebSocket\MessageEmitterInterface;

/**
 * The SwooleMessageEmitter class.
 */
class SwooleMessageTextEmitter implements MessageEmitterInterface
{
    public function __construct(protected Server $server)
    {
    }

    public function emit(int $fd, string $data): bool
    {
        return $this->server->push($fd, $data);
    }
}
