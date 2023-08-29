<?php

/**
 * Part of cati project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\WebSocket\Frame;
use Windwalker\Event\AbstractEvent;

/**
 * The MessageEvent class.
 */
class MessageEvent extends AbstractEvent
{
    use ServerEventTrait;

    protected Frame $frame;

    public function getFrame(): Frame
    {
        return $this->frame;
    }

    /**
     * @param  Frame  $frame
     *
     * @return  static  Return self to support chaining.
     */
    public function setFrame(Frame $frame): static
    {
        $this->frame = $frame;

        return $this;
    }
}
