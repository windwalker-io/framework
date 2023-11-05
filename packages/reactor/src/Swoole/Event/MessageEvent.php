<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;
use Windwalker\Reactor\WebSocket\WebSocketFrameInterface;

/**
 * The MessageEvent class.
 */
class MessageEvent extends AbstractEvent
{
    use ServerEventTrait;

    protected WebSocketFrameInterface $frame;

    public function getFrame(): WebSocketFrameInterface
    {
        return $this->frame;
    }

    /**
     * @param  WebSocketFrameInterface  $frame
     *
     * @return  static  Return self to support chaining.
     */
    public function setFrame(WebSocketFrameInterface $frame): static
    {
        $this->frame = $frame;

        return $this;
    }

    public function getFd(): int
    {
        return $this->frame->getFd();
    }

    public function getData(): string
    {
        return $this->frame->getData();
    }
}
