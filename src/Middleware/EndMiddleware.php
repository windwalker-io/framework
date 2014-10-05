<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Middleware;

/**
 * End Middleware, this object will not do anything.
 *
 * @since 2.0
 */
class EndMiddleware extends AbstractMiddleware
{
	/**
	 * Set next middleware.
	 *
	 * @param   object $object The middleware object.
	 *
	 * @return  EndMiddleware  Return self to support chaining.
	 */
	public function setNext($object)
	{
		return $this;
	}

	/**
	 * Get next middleware.
	 *
	 * @return  mixed
	 */
	public function getNext()
	{
		return null;
	}

	/**
	 * Call next middleware.
	 *
	 * @return  mixed
	 */
	public function call()
	{
	}
}
