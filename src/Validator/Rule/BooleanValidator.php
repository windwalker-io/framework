<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Validator\Rule;

/**
 * The BooleanValidator class.
 * 
 * @since  2.0
 */
class BooleanValidator extends RegexValidator
{
	/**
	 * The regular expression to use in testing  value.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $regex = '^(?:[01]|true|false)$';

	/**
	 * The regular expression modifiers to use when testing value.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $modifiers = 'i';

	/**
	 * Test value and return boolean
	 *
	 * @param mixed $value
	 *
	 * @return  boolean
	 */
	protected function test($value)
	{
		if (is_bool($value))
		{
			return true;
		}

		return parent::test($value);
	}
}
