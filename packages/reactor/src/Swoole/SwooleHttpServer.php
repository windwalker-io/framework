<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole;

use Swoole\Http\Server as SwooleNativeHttpServer;
use Windwalker\Http\Server\HttpServerInterface;

/**
 * The SwooleServer class.
 */
class SwooleHttpServer extends SwooleServer implements HttpServerInterface
{
    public static string $swooleServerClass = SwooleNativeHttpServer::class;
}
