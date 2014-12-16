<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Validator\Test;

use Windwalker\Validator\Rule\EmailValidator;

/**
 * Test class of EmailValidator
 *
 * @since 2.0
 */
class EmailValidatorTest extends AbstractValidateTestCase
{
	/**
	 * Test instance.
	 *
	 * @var EmailValidator
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
		$this->instance = new EmailValidator;
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
				'foo@gmail.com',
				true
			),
			array(
				'case2',
				'foo bar@gmail.com',
				false
			),
			array(
				'case3',
				'foo+bar@gmail.com',
				true
			),
			array(
				'case4',
				'foo.bar-yoo@gmail.com',
				true
			),
			array(
				'case5',
				'foo@gmail@com',
				false
			),
			array(
				'case6',
				'foo',
				false
			),
		);
	}
}
