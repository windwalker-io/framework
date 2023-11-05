<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The BeforeReloadEvent class.
 */
class BeforeReloadEvent extends AbstractEvent
{
    use ServerEventTrait;
}
