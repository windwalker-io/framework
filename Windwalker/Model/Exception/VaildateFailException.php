<?php

namespace Windwalker\Model\Exception;

use Exception;

class VaildateFailExcption extends \Exception
{
	/**
	 * Property errors.
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Constructor.
	 *
	 * @param array $errors
	 */
	public function __construct($errors)
	{
		$this->errors = $errors;

		parent::__construct();
	}

	/**
	 * getErrors
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * setErrors
	 *
	 * @param $errors
	 *
	 * @return $this
	 */
	public function setErrors($errors)
	{
		$this->errors = $errors;

		return $this;
	}
}
