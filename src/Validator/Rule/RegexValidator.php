<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Validator\Rule;

use Windwalker\Validator\AbstractValidator;

/**
 * The RegexValidator class.
 * 
 * @since  2.0
 */
class RegexValidator extends AbstractValidator
{
	/**
	 * The regular expression to use in testing value.
	 *
	 * @var  string
	 */
	protected $regex;

	/**
	 * The regular expression modifiers to use when testing a value.
	 *
	 * @var  string
	 */
	protected $modifiers = '';

	/**
	 * Class init.
	 *
	 * @param string $regex
	 * @param string $modifiers
	 */
	public function __construct($regex = null, $modifiers = '')
	{
		$this->modifiers = $modifiers ? : $this->modifiers;
		$this->regex     = $regex ? : $this->regex;
	}

	/**
	 * Method to get property Regex
	 *
	 * @return  string
	 */
	public function getRegex()
	{
		return $this->regex;
	}

	/**
	 * Method to set property regex
	 *
	 * @param   string $regex
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setRegex($regex)
	{
		$this->regex = $regex;

		return $this;
	}

	/**
	 * Method to get property Modifiers
	 *
	 * @return  string
	 */
	public function getModifiers()
	{
		return $this->modifiers;
	}

	/**
	 * Method to set property modifiers
	 *
	 * @param   string $modifiers
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setModifiers($modifiers)
	{
		$this->modifiers = $modifiers;

		return $this;
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
		if (!$this->regex)
		{
			return true;
		}

		// Test the value against the regular expression.
		return (bool) preg_match(chr(1) . $this->regex . chr(1) . $this->modifiers, (string) $value);
	}
}
