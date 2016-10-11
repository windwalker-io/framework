<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Structure\Test\Format;

use Windwalker\Structure\Format\JsonFormat;

/**
 * Test class of JsonFormat
 *
 * @since 2.0
 */
class JsonFormatTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var JsonFormat
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
		$this->instance = new JsonFormat;
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
	 * Method to test objectToString().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Structure\Format\JsonFormat::structToString
	 * @TODO   Implement testObjectToString().
	 */
	public function testObjectToString()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test stringToObject().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Structure\Format\JsonFormat::stringToStruct
	 * @TODO   Implement testStringToObject().
	 */
	public function testStringToObject()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
