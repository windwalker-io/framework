<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Authorisation;

/**
 * The CallbackPolicy class.
 *
 * @since  3.0-beta
 */
class CallbackPolicy implements PolicyInterface
{
	/**
	 * Property handler.
	 *
	 * @var  callable
	 */
	protected $handler;

	/**
	 * CallbackPolicy constructor.
	 *
	 * @param callable $handler
	 */
	public function __construct($handler)
	{
		$this->setHandler($handler);
	}

	/**
	 * authorise
	 *
	 * @param   mixed $user
	 * @param   mixed $data
	 *
	 * @return  boolean
	 */
	public function authorise($user, $data = null)
	{
		return call_user_func_array($this->handler, func_get_args());
	}

	/**
	 * Method to get property Handler
	 *
	 * @return  callable
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * Method to set property handler
	 *
	 * @param   callable $handler
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setHandler($handler)
	{
		if (!is_callable($handler))
		{
			throw new \InvalidArgumentException('Handler should be a valid callback');
		}

		$this->handler = $handler;

		return $this;
	}
}
