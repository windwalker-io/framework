<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole;

use Swoole\WebSocket\Server as SwooleNativeWebsocketServer;
use Windwalker\Http\Server\HttpServerInterface;

/**
 * The SwooleServer class.
 */
class SwooleWebsocketServer extends SwooleServer implements HttpServerInterface
{
    protected static function getSwooleServerClassName(): string
    {
        return SwooleNativeWebsocketServer::class;
    }
}
