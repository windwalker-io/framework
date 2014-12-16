<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
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
	public function triggerEvent($event, $args = array())
	{
		$this->dispatcher->triggerEvent($event, $args);
	}

	/**
	 * getDispatcher
	 *
	 * @return  \Windwalker\Event\Dispatcher
	 */
	public function getDispatcher()
	{
		return $this->dispatcher;
	}

	/**
	 * setDispatcher
	 *
	 * @param   \Windwalker\Event\Dispatcher $dispatcher
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDispatcher($dispatcher)
	{
		$this->dispatcher = $dispatcher;

		return $this;
	}
}
