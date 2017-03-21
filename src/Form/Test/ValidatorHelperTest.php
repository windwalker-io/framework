<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form\Test;

use Windwalker\Form\ValidatorHelper;

/**
 * Test class of ValidatorHelper
 *
 * @since 2.0
 */
class ValidatorHelperTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		ValidatorHelper::reset();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		ValidatorHelper::reset();
	}

	/**
	 * Method to test create().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Form\ValidatorHelper::create
	 */
	public function testCreate()
	{
		$filter = ValidatorHelper::create('ip');

		$this->assertInstanceOf('Windwalker\\Validator\\Rule\\IpValidator', $filter);

		$filter = ValidatorHelper::create('bar');

		$this->assertInstanceOf('Windwalker\\Validator\\Rule\\RegexValidator', $filter);

		$this->assertTrue($filter->validate('bar'));

		ValidatorHelper::addNamespace('Windwalker\\Form\\Test\\Stub');

		$filter = ValidatorHelper::create('stub');

		$this->assertInstanceOf('Windwalker\\Form\\Test\\Stub\\StubValidator', $filter);
	}

	/**
	 * testCreateByClassName
	 *
	 * @return  void
	 */
	public function testCreateByClassName()
	{
		$filter = ValidatorHelper::create('Windwalker\\Form\\Test\\Stub\\StubValidator');

		$this->assertInstanceOf('Windwalker\\Form\\Test\\Stub\\StubValidator', $filter);
	}
}
