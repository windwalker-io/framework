<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form\Test\Stub;

use Windwalker\Validator\AbstractValidator;

/**
 * The StubValidator class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class StubValidator extends AbstractValidator
{
	/**
	 * Test value and return boolean
	 *
	 * @param mixed $value
	 *
	 * @return  boolean
	 */
	protected function test($value)
	{
		$this->setError('Test Fail: ' . $value);

		return false;
	}
}
