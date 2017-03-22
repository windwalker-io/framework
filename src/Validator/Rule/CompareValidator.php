<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Validator\Rule;

use Windwalker\Compare\CompareHelper;
use Windwalker\Validator\AbstractValidator;

/**
 * The CompareValidator class.
 *
 * @since  __DEPLOY_VERSION__
 */
class CompareValidator extends AbstractValidator
{
	/**
	 * Property operator.
	 *
	 * @var  string
	 */
	protected $operator;

	/**
	 * Property compare.
	 *
	 * @var  mixed|null
	 */
	protected $compare;

	/**
	 * Property strict.
	 *
	 * @var  bool
	 */
	protected $strict;

	/**
	 * CompareValidator constructor.
	 *
	 * @param mixed  $compare
	 * @param string $operator
	 * @param bool   $strict
	 *
	 * @throws \DomainException
	 */
	public function __construct($compare = null, $operator = '', $strict = false)
	{
		if (!class_exists(CompareHelper::class))
		{
			throw new \DomainException('Please install windwalker/compare to support this Validator.');
		}

		$this->setOperator($operator);
		$this->compare = $compare;
		$this->setStrict($strict);
	}

	/**
	 * Test value and return boolean
	 *
	 * @param mixed $value
	 *
	 * @return  boolean
	 * @throws \InvalidArgumentException
	 */
	protected function test($value)
	{
		$compare = $this->compare;
		$operator = $this->operator;
		$strict = $this->strict;

		return CompareHelper::compare($value, $compare, $operator, $strict);
	}

	/**
	 * Method to get property Operator
	 *
	 * @return  string
	 */
	public function getOperator()
	{
		return $this->operator;
	}

	/**
	 * Method to set property operator
	 *
	 * @param   string $operator
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setOperator($operator)
	{
		$this->operator = strtolower(trim($operator));

		return $this;
	}

	/**
	 * Method to get property Compare
	 *
	 * @return  mixed|null
	 */
	public function getCompare()
	{
		return $this->compare;
	}

	/**
	 * Method to set property compare
	 *
	 * @param   mixed|null $compare
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setCompare($compare)
	{
		$this->compare = $compare;

		return $this;
	}

	/**
	 * Method to set property strict
	 *
	 * @param   bool $strict
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setStrict($strict)
	{
		$this->strict = (bool) $strict;

		return $this;
	}
}
