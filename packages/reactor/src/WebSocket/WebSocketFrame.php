<?php

/**
 * Part of cati project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\WebSocket;

/**
 * The WebSocketFrame class.
 */
class WebSocketFrame implements WebSocketFrameInterface
{
    public function __construct(protected int $fd, protected string $data = '')
    {
    }

    public function getFd(): int
    {
        return $this->fd;
    }

    /**
     * @param  int  $fd
     *
     * @return  static  Return self to support chaining.
     */
    public function withFd(int $fd): static
    {
        $new = clone $this;
        $new->fd = $fd;

        return $new;
    }

    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param  string  $data
     *
     * @return  static  Return self to support chaining.
     */
    public function withData(string $data): static
    {
        $new = clone $this;
        $new->data = $data;

        return $new;
    }
}
