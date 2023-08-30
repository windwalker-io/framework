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
 * The WebSocketFrame class.
 */
interface WebSocketFrameInterface
{
    public function getFd(): int;

    public function getData(): string;
}
