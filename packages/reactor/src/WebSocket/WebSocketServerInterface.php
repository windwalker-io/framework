<?php

declare(strict_types=1);

namespace Windwalker\Reactor\WebSocket;

/**
 * Interface WebSocketServerInterface
 */
interface WebSocketServerInterface
{
    public function getMessageEmitter(): MessageEmitterInterface;
}
