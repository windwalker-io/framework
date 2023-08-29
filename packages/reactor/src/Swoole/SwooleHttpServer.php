<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as SwooleNativeHttpServer;
use Swoole\Server as SwooleBaseServer;
use Swoole\Server\Port;
use Windwalker\Http\Factory\ServerRequestFactory;
use Windwalker\Http\Server\HttpServerInterface;
use Windwalker\Http\Server\HttpServerTrait;

/**
 * The SwooleServer class.
 *
 * @method $this onRequest(callable $handler)
 */
class SwooleHttpServer extends SwooleServer implements HttpServerInterface
{
    use HttpServerTrait;

    public static string $swooleServerClass = SwooleNativeHttpServer::class;

    protected function registerEvents(Port|SwooleBaseServer $port): void
    {
        $port->on(
            'request',
            function (Request $request, Response $response) {
                $psrRequest = ServerRequestFactory::createFromSwooleRequest($request, $this->getHost());
                $fd = $request->fd;

                $psrRequest = $psrRequest->withAttribute(
                    'swoole',
                    compact('fd', 'request', 'response')
                );

                $output = $this->createOutput($response);

                $this->handleRequest($psrRequest, $output);

                // Todo: Move this to config
                $garbageMax = 50;
                if ((memory_get_usage() / 1024 / 1024) > $garbageMax) {
                    gc_collect_cycles();
                }
            }
        );

        parent::registerEvents($port);
    }
}
