<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The StartEvent class.
 */
class StartEvent extends AbstractEvent
{
    use ServerEventTrait;
}
