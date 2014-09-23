<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Validator\Test;

use Windwalker\Validator\Rule\CreditcardValidator;

/**
 * Test class of CreditcardValidator
 *
 * @since {DEPLOY_VERSION}
 */
class CreditcardValidatorTest extends AbstractValidateTestCase
{
	/**
	 * Test instance.
	 *
	 * @var CreditcardValidator
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
		$this->instance = new CreditcardValidator;
	}

	/**
	 * testCase
	 *
	 * These fake numbers were generated from: http://www.getcreditcardnumbers.com/
	 *
	 * @return  array
	 */
	public function validateCase()
	{
		return array(
			array(
				'American Express',
				'378515770182856',
				true
			),
			array(
				'Visa',
				'4509782003875110',
				true
			),
			array(
				'Discover',
				'6011483235207596',
				true
			),
			array(
				'MasterCard',
				'5110555858557787',
				true
			),
			array(
				'Diners Club',
				'30333189575193',
				true
			),
			array(
				'Not valid',
				'1234567887654321',
				false
			),
		);
	}
}
