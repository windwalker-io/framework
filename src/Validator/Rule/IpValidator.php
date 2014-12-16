<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Validator\Rule;

/**
 * The IpValidator class.
 * 
 * @since  2.0
 */
class IpValidator extends RegexValidator
{
	/**
	 * The regular expression to use in testing value.
	 *
	 * @var  string
	 */
	protected $regex = '^((?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))*$';

	/**
	 * The regular expression modifiers to use when testing a value.
	 *
	 * @var  string
	 */
	protected $modifiers = 'i';
}
