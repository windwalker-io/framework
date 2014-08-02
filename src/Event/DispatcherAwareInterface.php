<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Event;

use Windwalker\Event\Event\EventInterface;

/**
 * Interface DispatcherAwareInterface
 */
interface DispatcherAwareInterface
{
	/**
	 * Trigger an event.
	 *
	 * @param   EventInterface|string  $event  The event object or name.
	 *
	 * @return  EventInterface  The event after being passed through all listeners.
	 *
	 * @since   1.0
	 */
	public function triggerEvent($event);
}

