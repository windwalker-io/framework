<?php

/**
 * Part of cati project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\WebSocket;

/**
 * Interface WebSocketEmitterInterface
 */
interface MessageEmitterInterface
{
    public function emit(int $fd, string $data): bool;
}
