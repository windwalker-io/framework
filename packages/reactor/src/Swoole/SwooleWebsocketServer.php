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
use Windwalker\Http\Server\HttpServerTrait;

/**
 * The SwooleServer class.
 */
class SwooleWebsocketServer extends SwooleServer implements HttpServerInterface
{
    use HttpServerTrait;

    public static string $swooleServerClass = SwooleNativeWebsocketServer::class;
}
