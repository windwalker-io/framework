<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The BeforeShutdownEvent class.
 */
class BeforeShutdownEvent extends AbstractEvent
{
    use ServerEventTrait;
}
