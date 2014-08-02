<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Validator;

/**
 * The ValidatorInterface class.
 * 
 * @since  {DEPLOY_VERSION}
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
