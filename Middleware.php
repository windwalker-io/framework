<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Middleware;

/**
 * Class Middleware
 *
 * @since 1.0
 */
abstract class Middleware implements MiddleInterface
{
	/**
	 * Property next.
	 *
	 * @var  object
	 */
	protected $next = null;

	/**
	 * getNext
	 *
	 * @return  object
	 */
	public function getNext()
	{
		return $this->next;
	}

	/**
	 * setNext
	 *
	 * @param   object $object
	 *
	 * @return  Middleware  Return self to support chaining.
	 */
	public function setNext($object)
	{
		$this->next = $object;

		return $this;
	}
}
