<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Validator\Test;

use Windwalker\Validator\Rule\NoneValidator;

/**
 * Test class of NoneValidator
 *
 * @since 2.0
 */
class NoneValidatorTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var NoneValidator
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
		$this->instance = new NoneValidator;
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
		$this->assertTrue($this->instance->validate('abc2v095iw354069uj92$%&%$ETV E^123cba456'));
	}
}
