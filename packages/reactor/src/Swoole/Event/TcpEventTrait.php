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
}
