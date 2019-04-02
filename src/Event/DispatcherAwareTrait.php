<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
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
        return $this->dispatcher->triggerEvent($event, $args);
    }

    /**
     * Add a listener to this dispatcher, only if not already registered to these events.
     * If no events are specified, it will be registered to all events matching it's methods name.
     * In the case of a closure, you must specify at least one event name.
     *
     * @param   object|callable $listener     The listener
     * @param   array|integer   $priorities   An associative array of event names as keys
     *                                        and the corresponding listener priority as values.
     *
     * @return  static  This method is chainable.
     *
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    public function addListener($listener, $priorities = [])
    {
        $this->getDispatcher()->addListener($listener, $priorities);

        return $this;
    }

    /**
     * on
     *
     * @param string   $event
     * @param callable $callable
     * @param int      $priority
     *
     * @return  static
     */
    public function listen($event, $callable, $priority = ListenerPriority::NORMAL)
    {
        return $this->addListener($callable, [$event => $priority]);
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
