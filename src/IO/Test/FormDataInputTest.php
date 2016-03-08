<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\IO\Test;

use Windwalker\IO\FormDataInput;
use Windwalker\Test\TestHelper;

/**
 * Test class of FormDataInput
 *
 * @since 2.1.7
 */
class FormDataInputTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var FormDataInput
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
		$this->instance = new FormDataInput(array());
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
	 * Test the Windwalker\IO\Json::__construct method.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\IO\Json::__construct
	 * @since   2.0
	 */
	public function test__construct()
	{
		$this->assertInstanceOf(
			'Windwalker\Filter\InputFilter',
			TestHelper::getValue($this->instance, 'filter')
		);

		$this->assertEmpty(
			TestHelper::getValue($this->instance, 'data')
		);

		// Given Source & filter
		$src = array('foo' => 'bar');
		$input = new FormDataInput($src);

		$this->assertEquals(
			$src,
			TestHelper::getValue($input, 'data')
		);

		// Src from GLOBAL
		FormDataInput::setRawData(null);

		$_SERVER['CONTENT_TYPE'] = 'multipart/form-data; boundary=----WebKitFormBoundary8zi5vcW6H9OgqKSj';

		$GLOBALS['HTTP_RAW_POST_DATA'] = <<<DATA
------WebKitFormBoundary8zi5vcW6H9OgqKSj
Content-Disposition: form-data; name="flower"

SAKURA
------WebKitFormBoundary8zi5vcW6H9OgqKSj
Content-Disposition: form-data; name="tree"

Marabutan
------WebKitFormBoundary8zi5vcW6H9OgqKSj
Content-Disposition: form-data; name="fruit"

Apple
------WebKitFormBoundary8zi5vcW6H9OgqKSj--
DATA;

		$input = new FormDataInput;

		$this->assertEquals(
			array('flower' => 'SAKURA', 'tree' => 'Marabutan', 'fruit' => 'Apple'),
			TestHelper::getValue($input, 'data')
		);
	}

	/**
	 * Method to test getRawData().
	 *
	 * @return void
	 *
	 * @covers Windwalker\IO\FormDataInput::getRawData
	 * @TODO   Implement testGetRawData().
	 */
	public function testGetRawData()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test parseFormData().
	 *
	 * @return void
	 *
	 * @covers Windwalker\IO\FormDataInput::parseFormData
	 * @TODO   Implement testParseFormData().
	 */
	public function testParseFormData()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
