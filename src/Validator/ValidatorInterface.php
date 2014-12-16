<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Validator;

/**
 * The ValidatorInterface class.
 * 
 * @since  2.0
 */
interface ValidatorInterface
{
	/**
	 * Test this value.
	 *
	 * @param mixed $value
	 *
	 * @return  boolean
	 */
	public function validate($value);

	/**
	 * Get error message.
	 *
	 * @return  string
	 */
	public function getError();
}
