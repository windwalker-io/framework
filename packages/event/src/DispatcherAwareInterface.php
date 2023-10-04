<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Interface DispatcherAwareInterface
 */
interface DispatcherAwareInterface
{
    /**
     * getDispatcher
     *
     * @return  EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface;
}
