<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Validator\Test;

use Windwalker\Validator\Rule\ColorValidator;

/**
 * Test class of ColorValidator
 *
 * @since 2.0
 */
class ColorValidatorTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var ColorValidator
	 */
	protected $instance;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->instance = new ColorValidator;
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
	 * testValidate
	 *
	 * @return  void
	 *
	 * @cover  \Windwalker\Validator\Rule\AlnumValidator
	 */
	public function testValidate()
	{
		$this->assertTrue($this->instance->validate('#f2f2f2'));
		$this->assertTrue($this->instance->validate('#F2F2F2'));
		$this->assertTrue($this->instance->validate('#F2F'));
		$this->assertTrue($this->instance->validate('#000'));

		$this->assertFalse($this->instance->validate('#F2#$F2'));
		$this->assertFalse($this->instance->validate('f2f2f2'));
		$this->assertFalse($this->instance->validate('#12345678'));
		$this->assertFalse($this->instance->validate('#UUUUUU'));
	}
}
