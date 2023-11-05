<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Http\Request;
use Windwalker\Event\AbstractEvent;
use Windwalker\Reactor\WebSocket\WebSocketRequest;

/**
 * The OpenEvent class.
 */
class OpenEvent extends AbstractEvent
{
    use ServerEventTrait;

    protected WebSocketRequest $request;

    protected Request $swooleRequest;

    public function getRequest(): WebSocketRequest
    {
        return $this->request;
    }

    /**
     * @param  WebSocketRequest  $request
     *
     * @return  static  Return self to support chaining.
     */
    public function setRequest(WebSocketRequest $request): static
    {
        $this->request = $request;

        return $this;
    }

    public function getSwooleRequest(): Request
    {
        return $this->swooleRequest;
    }

    /**
     * @param  Request  $swooleRequest
     *
     * @return  static  Return self to support chaining.
     */
    public function setSwooleRequest(Request $swooleRequest): static
    {
        $this->swooleRequest = $swooleRequest;

        return $this;
    }
}
