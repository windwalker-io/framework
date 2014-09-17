<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Validator\Rule;

use Windwalker\Validator\AbstractValidator;

/**
 * The RegexValidator class.
 * 
 * @since  {DEPLOY_VERSION}
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
		return (bool) preg_match(chr(1) . $this->regex . chr(1) . $this->modifiers, $value);
	}
}
