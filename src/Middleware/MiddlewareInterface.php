<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Middleware;

/**
 * Class MiddlewareInterface
 *
 * @since 1.0
 */
interface MiddlewareInterface
{
	/**
	 * call
	 *
	 * @return  mixed
	 */
	public function call();

	/**
	 * getNext
	 *
	 * @return  mixed
	 */
	public function getNext();

	/**
	 * setNext
	 *
	 * @param   object $object
	 *
	 * @return  Middleware  Return self to support chaining.
	 */
	public function setNext($object);
}
