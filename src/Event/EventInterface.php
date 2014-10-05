<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Event;

/**
 * Class EventInterface
 *
 * @since {DEPLOY_VERSION}
 */
interface EventInterface
{
	/**
	 * Get the event name.
	 *
	 * @return  string  The event name.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getName();

	/**
	 * Tell if the event propagation is stopped.
	 *
	 * @return  boolean  True if stopped, false otherwise.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function isStopped();
}

