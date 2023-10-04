<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole;

use Swoole\WebSocket\Frame;
use Windwalker\Reactor\WebSocket\WebSocketFrameInterface;

/**
 * The WebSocketFrame class.
 */
class WebSocketFrameWrapper implements WebSocketFrameInterface
{
    public function __construct(protected Frame $frame)
    {
    }

    public function getFrame(): Frame
    {
        return $this->frame;
    }

    public function withFrame(Frame $frame): static
    {
        $new = clone $this;

        $new->frame = $frame;

        return $new;
    }

    public function getFd(): int
    {
        return $this->frame->fd;
    }

    public function getData(): string
    {
        return $this->frame->data;
    }
}
