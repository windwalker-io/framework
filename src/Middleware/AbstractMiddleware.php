<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Middleware;

/**
 * Basic Middleware class.
 *
 * @since 2.0
 */
abstract class AbstractMiddleware implements MiddlewareInterface
{
	/**
	 * THe next middleware.
	 *
	 * @var  mixed|MiddlewareInterface
	 */
	protected $next = null;

	/**
	 * Get next middleware.
	 *
	 * @return  mixed|MiddlewareInterface
	 */
	public function getNext()
	{
		return $this->next;
	}

	/**
	 * Set next middleware.
	 *
	 * @param   object|MiddlewareInterface $object The middleware object.
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setNext($object)
	{
		if (!($object instanceof MiddlewareInterface) && is_callable($object))
		{
			$object = new CallbackMiddleware($object);
		}

		$this->next = $object;

		return $this;
	}
}
