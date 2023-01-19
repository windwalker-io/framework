<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Server as SwooleServer;
use Windwalker\Http\Request\ServerRequestFactory;

/**
 * The SwooleServer class.
 */
class SwooleHttpServer extends AbstractServer
{
    protected ?SwooleServer $swooleServer = null;

    protected int $mode = SWOOLE_BASE;

    protected int $sockType = SWOOLE_TCP;

    public function __construct()
    {
        //
    }

    public function setConfig(array $config): bool
    {
        return $this->swooleServer->set($config);
    }

    public function listen(string $host = '0.0.0.0', int $port = 0, array $options = []): void
    {
        $server = $this->getSwooleServer($host, $port, $this->mode, $this->sockType);

        $server->on(
            'request',
            function (Request $request, Response $response) {
                ServerRequestFactory::
            }
        );

        $server->start();
    }

    public function stop(int $workerId = -1, bool $waitEvent = false): void
    {
        $this->getSwooleServer()->stop($workerId, $waitEvent);
    }

    public function getSwooleServer(
        string $host = '0.0.0.0',
        int $port = 0,
        int $mode = SWOOLE_BASE,
        int $sockType = SWOOLE_SOCK_TCP
    ): SwooleServer {
        return $this->swooleServer ??= static::createSwooleServer($host, $port, $mode, $sockType);
    }

    public static function createSwooleServer(
        string $host = '0.0.0.0',
        int $port = 0,
        int $mode = SWOOLE_BASE,
        int $sockType = SWOOLE_SOCK_TCP
    ): SwooleServer {
        return new SwooleServer($host, $port, $mode, $sockType);
    }

    /**
     * @return int
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * @param  int  $mode
     *
     * @return  static  Return self to support chaining.
     */
    public function setMode(int $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @return int
     */
    public function getSockType(): int
    {
        return $this->sockType;
    }

    /**
     * @param  int  $sockType
     *
     * @return  static  Return self to support chaining.
     */
    public function setSockType(int $sockType): static
    {
        $this->sockType = $sockType;

        return $this;
    }
}
