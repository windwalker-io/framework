<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
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
	public function execute()
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

