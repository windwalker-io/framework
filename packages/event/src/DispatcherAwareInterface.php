<?php

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
