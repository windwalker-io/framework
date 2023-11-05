<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Windwalker\Event\AbstractEvent;

/**
 * The HandshakeEvent class.
 */
class HandshakeEvent extends AbstractEvent
{
    protected Request $request;

    protected Response $response;

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): static
    {
        $this->response = $response;

        return $this;
    }
}
