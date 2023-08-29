<?php

/**
 * Part of cati project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Psr\Http\Message\RequestInterface;
use Windwalker\Event\AbstractEvent;

/**
 * The OpenEvent class.
 */
class OpenEvent extends AbstractEvent
{
    use ServerEventTrait;

    protected RequestInterface $request;

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @param  RequestInterface  $request
     *
     * @return  static  Return self to support chaining.
     */
    public function setRequest(RequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }
}
