<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

/**
 * Trait TcpEventTrait
 */
trait TcpEventTrait
{
    protected int $fd;

    protected int $reactorId;

    public function getFd(): int
    {
        return $this->fd;
    }

    public function setFd(int $fd): static
    {
        $this->fd = $fd;

        return $this;
    }

    public function getReactorId(): int
    {
        return $this->reactorId;
    }

    public function setReactorId(int $reactorId): static
    {
        $this->reactorId = $reactorId;

        return $this;
    }
}
