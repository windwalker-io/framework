<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Validator\Rule;

/**
 * The EmailValidator class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class EmailValidator extends RegexValidator
{
	/**
	 * The regular expression to use in testing a form field value.
	 *
	 * @var  string
	 * @see  http://www.w3.org/TR/html-markup/input.email.html
	 */
	protected $regex = '^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$';
}
