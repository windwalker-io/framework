<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 $Asikart.
 * @license    __LICENSE__
 */

namespace Windwalker\Validator;

use Windwalker\Validator\Rule\CallbackValidator;

/**
 * The ValidatorComposite class.
 *
 * @since  3.2
 */
class ValidatorComposite extends AbstractValidator
{
	const MODE_MATCH_ALL = 1;
	const MODE_MATCH_ONE = 2;

	/**
	 * Property validators.
	 *
	 * @var  ValidatorInterface[]
	 */
	protected $validators = [];

	/**
	 * Property errors.
	 *
	 * @var  array
	 */
	protected $errors = [];

	/**
	 * Property results.
	 *
	 * @var  bool[]
	 */
	protected $results = [];

	/**
	 * Property mode.
	 *
	 * @var  int
	 */
	protected $mode;

	/**
	 * ValidatorComposite constructor.
	 *
	 * @param ValidatorInterface[] $validators
	 * @param int                  $mode
	 */
	public function __construct(array $validators = [], $mode = self::MODE_MATCH_ALL)
	{
		$this->setValidators($validators);
		$this->mode = $mode;
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
		$errorMessages = [];
		$results = [];

		foreach ($this->validators as $validator)
		{
			if (!$result = $validator->validate($value))
			{
				$errorMessages[] = $validator->getError();
			}

			$results[] = $result;
		}

		if ($this->mode === static::MODE_MATCH_ALL)
		{
			$bool = !in_array(false, $results, true);
		}
		else
		{
			$bool = in_array(true, $results, true);
		}

		if (!$bool)
		{
			$this->setError($this->getMessage());
			$this->setErrors($errorMessages);
		}
		else
		{
			$this->setError('');
			$this->setErrors([]);
		}

		$this->results = $results;

		return $bool;
	}

	/**
	 * addValidator
	 *
	 * @param ValidatorInterface|callable $validator
	 *
	 * @return  static
	 * @throws \InvalidArgumentException
	 */
	public function addValidator($validator)
	{
		if (!$validator instanceof ValidatorInterface)
		{
			if (!is_callable($validator))
			{
				throw new \InvalidArgumentException('Validator should be callable or ValidatorInterface.');
			}

			$validator = new CallbackValidator($validator);
		}

		$this->validators[] = $validator;

		return $this;
	}

	/**
	 * Method to get property Validators
	 *
	 * @return  ValidatorInterface[]
	 */
	public function getValidators()
	{
		return $this->validators;
	}

	/**
	 * Method to set property validators
	 *
	 * @param   ValidatorInterface[] $validators
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setValidators(array $validators)
	{
		foreach ($validators as $validator)
		{
			if (is_string($validator) && is_subclass_of($validator, ValidatorInterface::class))
			{
				$validator = new $validator;
			}

			$this->addValidator($validator);
		}

		return $this;
	}

	/**
	 * Method to get property Errors
	 *
	 * @return  array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Method to set property errors
	 *
	 * @param   array $errors
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setErrors($errors)
	{
		$this->errors = $errors;

		return $this;
	}

	/**
	 * Method to get property Results
	 *
	 * @return  bool[]
	 */
	public function getResults()
	{
		return $this->results;
	}

	/**
	 * Method to get property Mode
	 *
	 * @return  int
	 */
	public function getMode()
	{
		return $this->mode;
	}

	/**
	 * Method to set property mode
	 *
	 * @param   int $mode
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setMode($mode)
	{
		$this->mode = $mode;

		return $this;
	}
}
