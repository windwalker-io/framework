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
	 * @param   callable|MiddlewareInterface $callable The middleware object.
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setNext($callable)
	{
		if (!($callable instanceof MiddlewareInterface) && is_callable($callable))
		{
			$callable = new CallbackMiddleware($callable);
		}

		$this->next = $callable;

		return $this;
	}
}
