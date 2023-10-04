<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event;

/**
 * Interface EventAwareInterface
 */
interface EventAwareInterface extends EventListenableInterface, EventEmitterInterface, DispatcherAwareInterface
{
}
