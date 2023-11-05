<?php

declare(strict_types=1);

namespace Windwalker\Event;

/**
 * Interface EventAwareInterface
 */
interface EventAwareInterface extends EventListenableInterface, EventEmitterInterface, DispatcherAwareInterface
{
}
