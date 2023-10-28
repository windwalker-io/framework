<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
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
        if (!$this->server->exists($fd)) {
            return false;
        }

        return $this->server->push($fd, $data);
    }
}
