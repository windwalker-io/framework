<?php
/**
 * Part of windwalker-middleware project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Middleware;

/**
 * Class EndMiddleware
 *
 * @since 1.0
 */
class EndMiddleware extends Middleware
{
	/**
	 * setNext
	 *
	 * @param object $object
	 *
	 * @return  EndMiddleware
	 */
	public function setNext($object)
	{
		return $this;
	}

	/**
	 * getNext
	 *
	 * @return  null
	 */
	public function getNext()
	{
		return null;
	}

	/**
	 * call
	 *
	 * @return  void
	 */
	public function call()
	{
	}
}
 