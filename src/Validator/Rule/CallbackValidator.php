<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Validator\Rule;

use Windwalker\Validator\AbstractValidator;

/**
 * The CallbackValidator class.
 *
 * @since  __DEPLOY_VERSION__
 */
class CallbackValidator extends AbstractValidator
{
	/**
	 * Property handler.
	 *
	 * @var  callable
	 */
	protected $handler;

	/**
	 * CallbackValidator constructor.
	 *
	 * @param callable $handler
	 */
	public function __construct(callable $handler = null)
	{
		$this->handler = $handler;
	}

	/**
	 * Test value and return boolean
	 *
	 * @param mixed $value
	 *
	 * @return  boolean
	 */
	protected function test($value)
	{
		if (!$this->handler)
		{
			return true;
		}

		$handler = $this->handler;

		return $handler($value);
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
	public function setHandler(callable $handler)
	{
		$this->handler = $handler;

		return $this;
	}
}
