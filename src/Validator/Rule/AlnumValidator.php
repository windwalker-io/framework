<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Validator\Rule;

/**
 * The AlnumValidator class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class AlnumValidator extends RegexValidator
{
	/**
	 * The regular expression to use in testing value.
	 *
	 * @var  string
	 */
	protected $regex = '^[a-zA-Z0-9]*$';

	/**
	 * The regular expression modifiers to use when testing a value.
	 *
	 * @var  string
	 */
	protected $modifiers = 'i';
}
