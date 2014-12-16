<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Validator\Test;

use Windwalker\Validator\Rule\PhoneValidator;

/**
 * Test class of PhoneValidator
 *
 * @since 2.0
 */
class PhoneValidatorTest extends AbstractValidateTestCase
{
	/**
	 * Test instance.
	 *
	 * @var PhoneValidator
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
		$this->instance = new PhoneValidator;
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
	 * testCase
	 *
	 * @return  array
	 */
	public function validateCase()
	{
		return array(
			array(
				'case1',
				'0225647186',
				true
			),
			array(
				'case2',
				'0-800-456-7890',
				true
			),
			array(
				'555-365-4785',
				'',
				true
			),
			array(
				'case4',
				'4684521546874',
				true
			),
		);
	}
}
