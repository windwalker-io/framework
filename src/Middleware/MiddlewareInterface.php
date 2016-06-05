<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
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
	public function execute();

	/**
	 * Get next middleware.
	 *
	 * @return  mixed|MiddlewareInterface
	 */
	public function getNext();

	/**
	 * Set next middleware.
	 *
	 * @param   object|MiddlewareInterface $callable The middleware object.
	 *
	 * @return  MiddlewareInterface  Return self to support chaining.
	 */
	public function setNext($callable);
}
