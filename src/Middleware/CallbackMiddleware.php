<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Middleware;

/**
 * Callback Middleware
 *
 * @since 2.0
 */
class CallbackMiddleware extends AbstractMiddleware
{
	/**
	 * The callback handler.
	 *
	 * @var  callable
	 */
	protected $handler = null;

	/**
	 * Constructor.
	 *
	 * @param callable $handler The callback handler.
	 * @param object   $next    Next middleware.
	 */
	public function __construct($handler = null, $next = null)
	{
		$this->handler = $handler;
		$this->next    = $next;
	}

	/**
	 * Call next middleware.
	 *
	 * @return  mixed
	 */
	public function call()
	{
		return call_user_func($this->handler, $this->next);
	}

	/**
	 * Get callback handler.
	 *
	 * @return  callable The callback handler.
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * Set callback handler.
	 *
	 * @param   callable $handler The callback handler.
	 *
	 * @return  CallbackMiddleware  Return self to support chaining.
	 */
	public function setHandler($handler)
	{
		$this->handler = $handler;

		return $this;
	}
}

