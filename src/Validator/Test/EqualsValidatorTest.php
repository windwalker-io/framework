<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Validator\Test;

use Windwalker\Validator\Rule\EqualsValidator;

/**
 * Test class of EqualsValidator
 *
 * @since {DEPLOY_VERSION}
 */
class EqualsValidatorTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * getInstance
	 *
	 * @param string $compare
	 * @param bool   $strict
	 *
	 * @return  EqualsValidator
	 */
	protected function getInstance($compare, $strict = false)
	{
		return new EqualsValidator($compare, $strict);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Method to test test().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Validator\Rule\EqualsValidator::test
	 */
	public function testValidate()
	{
		$this->assertTrue($this->getInstance('abc')->validate('abc'));

		$this->assertTrue($this->getInstance('1')->validate(1));

		$this->assertTrue($this->getInstance(true)->validate(1));

		$this->assertFalse($this->getInstance(true, true)->validate(1));

		$this->assertFalse($this->getInstance(1, true)->validate('1'));

		$this->assertFalse($this->getInstance(1.5, true)->validate('1.5'));
	}
}
