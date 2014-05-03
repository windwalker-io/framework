<?php
/**
 * Part of windwalker-middleware project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Middleware;

/**
 * End Middleware, this object will not do anything.
 *
 * @since 2.0
 */
class EndMiddleware extends Middleware
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
