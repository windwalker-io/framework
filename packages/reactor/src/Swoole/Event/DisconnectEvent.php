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

/**
 * The DisconnectEvent class.
 */
class DisconnectEvent extends AbstractEvent
{
    use ServerEventTrait;

    protected int $fd;

    public function getFd(): int
    {
        return $this->fd;
    }

    /**
     * @param  int  $fd
     *
     * @return  static  Return self to support chaining.
     */
    public function setFd(int $fd): static
    {
        $this->fd = $fd;

        return $this;
    }
}
