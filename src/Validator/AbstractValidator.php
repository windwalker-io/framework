<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Validator;

/**
 * The AbstractValidator class.
 * 
 * @since  {DEPLOY_VERSION}
 */
abstract class AbstractValidator implements ValidatorInterface
{
	/**
	 * Property error.
	 *
	 * @var string
	 */
	protected $error = '';

	/**
	 * Property message.
	 *
	 * @var string
	 */
	protected $message = '';

	/**
	 * Property multiple.
	 *
	 * @var  boolean
	 */
	protected $multiple = false;

	/**
	 * Validate this value and set error message..
	 *
	 * @param mixed $value
	 *
	 * @return  boolean
	 */
	public function validate($value)
	{
		if (!$this->test($value))
		{
			$this->setError($this->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Test value and return boolean
	 *
	 * @param mixed $value
	 *
	 * @return  boolean
	 */
	abstract protected function test($value);

	/**
	 * Get error message.
	 *
	 * @return  string
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Method to set property error
	 *
	 * @param   string $error
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setError($error = null)
	{
		$this->error = $error ? : $this->getMessage();

		return $this;
	}

	/**
	 * Set error message.
	 *
	 * @param string $message
	 *
	 * @return  static
	 */
	public function setMessage($message)
	{
		$this->message = $message;

		return $this;
	}

	/**
	 * Method to get property Message
	 *
	 * @return  string
	 */
	protected function getMessage()
	{
		return $this->message;
	}
}
