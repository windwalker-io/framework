<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The AfterReloadEvent class.
 */
class AfterReloadEvent extends AbstractEvent
{
    use ServerEventTrait;
}
