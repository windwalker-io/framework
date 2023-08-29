<?php

/**
 * Part of cati project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Http\Server;
use Windwalker\Http\Server\ServerInterface;
use Windwalker\Reactor\Swoole\SwooleHttpServer;
use Windwalker\Reactor\Swoole\SwooleServer;

/**
 * Trait ServerEventTrait
 */
trait ServerEventTrait
{
    protected Server $swooleServer;

    protected ServerInterface $server;

    public function getSwooleServer(): Server
    {
        return $this->swooleServer;
    }

    /**
     * @param  Server  $swooleServer
     *
     * @return  static  Return self to support chaining.
     */
    public function setSwooleServer(Server $swooleServer): static
    {
        $this->swooleServer = $swooleServer;

        return $this;
    }

    public function getServer(): ServerInterface
    {
        return $this->server;
    }

    /**
     * @param  ServerInterface  $server
     *
     * @return  static  Return self to support chaining.
     */
    public function setServer(ServerInterface $server): static
    {
        $this->server = $server;

        return $this;
    }
}
