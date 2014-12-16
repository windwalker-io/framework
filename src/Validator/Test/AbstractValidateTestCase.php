<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Validator\Test;

use Windwalker\Validator\AbstractValidator;

/**
 * The ValidateTestCase class.
 * 
 * @since  2.0
 */
abstract class AbstractValidateTestCase extends  \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var AbstractValidator
	 */
	protected $instance;

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
	 * testValidate
	 *
	 * @param string  $name
	 * @param string  $input
	 * @param boolean $expect
	 *
	 * @return  void
	 *
	 * @cover  \Windwalker\Validator\Rule\AlnumValidator
	 *
	 * @dataProvider validateCase
	 */
	public function testValidate($name, $input, $expect)
	{
		if ($expect)
		{
			$this->assertTrue($this->instance->validate($input), 'Validate case: ' . $name . ' should be TRUE but FALSE. Input: ' . $input);
		}
		else
		{
			$this->assertFalse($this->instance->validate($input), 'Validate case: ' . $name . ' should be FALSE but TRUE. Input: ' . $input);
		}
	}
}
