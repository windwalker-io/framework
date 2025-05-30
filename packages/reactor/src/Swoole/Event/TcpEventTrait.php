<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

/**
 * Trait TcpEventTrait
 */
trait TcpEventTrait
{
    public int $fd;

    public int $reactorId;

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
