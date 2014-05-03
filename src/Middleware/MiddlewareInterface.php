<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Middleware;

/**
 * Middleware Interface
 *
 * @since 2.0
 */
interface MiddlewareInterface
{
	/**
	 * Call next middleware.
	 *
	 * @return  mixed
	 */
	public function call();

	/**
	 * Get next middleware.
	 *
	 * @return  mixed
	 */
	public function getNext();

	/**
	 * Set next middleware.
	 *
	 * @param   object $object The middleware object.
	 *
	 * @return  MiddlewareInterface  Return self to support chaining.
	 */
	public function setNext($object);
}
