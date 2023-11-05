<?php

declare(strict_types=1);

namespace Windwalker\Reactor\WebSocket;

/**
 * Interface WebSocketEmitterInterface
 */
interface MessageEmitterInterface
{
    public function emit(int $fd, string $data): bool;
}
