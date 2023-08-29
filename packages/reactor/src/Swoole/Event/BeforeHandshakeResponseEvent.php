<?php

/**
 * Part of cati project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Http\Request;
use Windwalker\Event\AbstractEvent;
use Windwalker\Http\Response\Response;

/**
 * The BeforeHandshakeResponseEvent class.
 */
class BeforeHandshakeResponseEvent extends AbstractEvent
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
