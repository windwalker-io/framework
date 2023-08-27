<?php

/**
 * Part of cati project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http\Event\Swoole;

use Swoole\Http\Server;
use Windwalker\Event\AbstractEvent;
use Windwalker\Http\Server\SwooleHttpServer;

/**
 * The StartEvent class.
 */
class StartEvent extends AbstractEvent
{
    protected Server $swooleServer;

    protected SwooleHttpServer $httpServer;

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

    public function getHttpServer(): SwooleHttpServer
    {
        return $this->httpServer;
    }

    /**
     * @param  SwooleHttpServer  $httpServer
     *
     * @return  static  Return self to support chaining.
     */
    public function setHttpServer(SwooleHttpServer $httpServer): static
    {
        $this->httpServer = $httpServer;

        return $this;
    }
}
