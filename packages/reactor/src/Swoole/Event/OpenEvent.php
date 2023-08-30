<?php

/**
 * Part of cati project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;
use Windwalker\Reactor\WebSocket\WebSocketRequest;

/**
 * The OpenEvent class.
 */
class OpenEvent extends AbstractEvent
{
    use ServerEventTrait;

    protected WebSocketRequest $request;

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
}
