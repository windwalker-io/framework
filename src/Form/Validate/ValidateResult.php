<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form\Validate;

use Windwalker\Form\Field\FieldInterface;

/**
 * The ValidateResult class.
 *
 * @since  {DEPLOY_VERSION}
 */
class ValidateResult
{
	const STATUS_SUCCESS = 200;

	const STATUS_REQUIRED = 400;

	const STATUS_FAILURE = 500;

	/**
	 * Property success.
	 *
	 * @var  boolean
	 */
	protected $result = self::STATUS_SUCCESS;

	/**
	 * Property message.
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * Property field.
	 *
	 * @var FieldInterface
	 */
	protected $field;

	/**
	 * Class init.
	 *
	 * @param integer|boolean $result
	 * @param string          $message
	 * @param FieldInterface  $field
	 */
	public function __construct($result = self::STATUS_SUCCESS, $message = null, FieldInterface $field = null)
	{
		$this->field   = $field;
		$this->message = $message;
		$this->result  = $result;
	}

	/**
	 * isSuccess
	 *
	 * @return  boolean
	 */
	public function isSuccess()
	{
		if ($this->result === true || $this->result == static::STATUS_SUCCESS)
		{
			return true;
		}

		return false;
	}

	/**
	 * isFailure
	 *
	 * @return  boolean
	 */
	public function isFailure()
	{
		return !$this->isSuccess();
	}

	/**
	 * Method to get property Message
	 *
	 * @return  string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * Method to set property message
	 *
	 * @param   string $message
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setMessage($message)
	{
		$this->message = $message;

		return $this;
	}

	/**
	 * Method to get property Field
	 *
	 * @return  FieldInterface
	 */
	public function getField()
	{
		return $this->field;
	}

	/**
	 * Method to set property field
	 *
	 * @param   FieldInterface $field
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setField($field)
	{
		$this->field = $field;

		return $this;
	}

	/**
	 * Method to get property Result
	 *
	 * @return  boolean
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * Method to set property result
	 *
	 * @param   boolean $result
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setResult($result)
	{
		$this->result = $result;

		return $this;
	}
}
