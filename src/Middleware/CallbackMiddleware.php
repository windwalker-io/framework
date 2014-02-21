<?php
/**
 * Part of windwalker-middleware project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Middleware;

/**
 * Class CallbackMiddleware
 *
 * @since 1.0
 */
class CallbackMiddleware extends Middleware
{
	/**
	 * Property handler.
	 *
	 * @var  callable
	 */
	protected $handler = null;

	/**
	 * Constructor.
	 *
	 * @param callable $handler
	 * @param object   $next
	 */
	public function __construct($handler = null, $next = null)
	{
		$this->handler = $handler;
		$this->next    = $next;
	}

	/**
	 * call
	 *
	 * @return  mixed
	 */
	public function call()
	{
		return call_user_func($this->handler, $this->next);
	}

	/**
	 * getHandler
	 *
	 * @return  callable
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * setHandler
	 *
	 * @param   callable $handler
	 *
	 * @return  CallbackMiddleware  Return self to support chaining.
	 */
	public function setHandler($handler)
	{
		$this->handler = $handler;

		return $this;
	}
}
 