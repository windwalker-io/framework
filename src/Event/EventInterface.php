<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Event;

/**
 * Class EventInterface
 *
 * @since 2.0
 */
interface EventInterface
{
	/**
	 * Get the event name.
	 *
	 * @return  string  The event name.
	 *
	 * @since   2.0
	 */
	public function getName();

	/**
	 * Tell if the event propagation is stopped.
	 *
	 * @return  boolean  True if stopped, false otherwise.
	 *
	 * @since   2.0
	 */
	public function isStopped();
}

