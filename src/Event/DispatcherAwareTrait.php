<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Event;

/**
 * Class DispatcherAwareTrait
 *
 * @since 2.0
 */
trait DispatcherAwareTrait
{
    /**
     * Property dispatcher.
     *
     * @var  Dispatcher
     */
    protected $dispatcher = null;

    /**
     * Trigger an event.
     *
     * @param   EventInterface|string $event The event object or name.
     * @param   array                 $args  The arguments.
     *
     * @return  EventInterface  The event after being passed through all listeners.
     *
     * @since   2.0
     */
    public function triggerEvent($event, $args = [])
    {
        $this->dispatcher->triggerEvent($event, $args);
    }

    /**
     * getDispatcher
     *
     * @return  DispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * setDispatcher
     *
     * @param   DispatcherInterface $dispatcher
     *
     * @return  static  Return self to support chaining.
     */
    public function setDispatcher(DispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }
}
