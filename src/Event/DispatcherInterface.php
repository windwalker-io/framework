<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Event;

/**
 * Interface DispatcherInterface
 */
interface DispatcherInterface
{
	/**
	 * Trigger an event.
	 *
	 * @param   EventInterface|string  $event  The event object or name.
	 * @param   array                  $args   The arguments.
	 *
	 * @return  EventInterface  The event after being passed through all listeners.
	 */
	public function triggerEvent($event, $args = array());

	/**
	 * Add a listener to this dispatcher, only if not already registered to these events.
	 * If no events are specified, it will be registered to all events matching it's methods name.
	 * In the case of a closure, you must specify at least one event name.
	 *
	 * @param   object|\Closure  $listener    The listener
	 * @param   array|integer    $priorities  An associative array of event names as keys
	 *                                        and the corresponding listener priority as values.
	 *
	 * @return  DispatcherInterface  This method is chainable.
	 *
	 * @throws  \InvalidArgumentException
	 *
	 * @since   2.0
	 */
	public function addListener($listener, $priorities = array());

	/**
	 * Add single listener.
	 *
	 * @param string   $event
	 * @param callable $callable
	 * @param int      $priority
	 *
	 * @return  static
	 */
	public function listen($event, $callable, $priority = ListenerPriority::NORMAL);
}
