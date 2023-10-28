<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Reactor\WebSocket;

/**
 * Interface WebSocketServerInterface
 */
interface WebSocketServerInterface
{
    public function getMessageEmitter(): MessageEmitterInterface;
}
