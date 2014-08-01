<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Validator\Rule;

/**
 * The BooleanValidator class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class BooleanValidator extends RegexValidator
{
	/**
	 * The regular expression to use in testing  value.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $regex = '^(?:[01]|true|false)$';

	/**
	 * The regular expression modifiers to use when testing value.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $modifiers = 'i';
}
