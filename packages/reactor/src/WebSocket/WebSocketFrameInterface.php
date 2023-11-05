<?php

declare(strict_types=1);

namespace Windwalker\Reactor\WebSocket;

/**
 * The WebSocketFrame class.
 */
interface WebSocketFrameInterface
{
    public function getFd(): int;

    public function getData(): string;
}
