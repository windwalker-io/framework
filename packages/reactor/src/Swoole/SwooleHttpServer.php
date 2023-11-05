<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole;

use Swoole\Http\Server as SwooleNativeHttpServer;
use Windwalker\Http\Server\HttpServerInterface;

/**
 * The SwooleServer class.
 */
class SwooleHttpServer extends SwooleServer implements HttpServerInterface
{
    protected static function getSwooleServerClassName(): string
    {
        return SwooleNativeHttpServer::class;
    }
}
