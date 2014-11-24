<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Validator\Test;

use Windwalker\Validator\Rule\IpValidator;

/**
 * Test class of IpValidator
 *
 * @since {DEPLOY_VERSION}
 */
class IpValidatorTest extends AbstractValidateTestCase
{
	/**
	 * Test instance.
	 *
	 * @var IpValidator
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
		$this->instance = new IpValidator;
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
				'123.45.67.89',
				true
			),
			array(
				'case2',
				'127.0.0.1',
				true
			),
			array(
				'case3',
				'192.168.140.155',
				true
			),
			array(
				'case4',
				'654.321.123.456',
				false
			),
			array(
				'case5',
				'http://abc.com',
				false
			),
		);
	}
}
