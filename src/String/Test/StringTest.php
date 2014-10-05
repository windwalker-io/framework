<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\String\Test;

use Windwalker\String\String;

/**
 * Test class of String
 *
 * @since {DEPLOY_VERSION}
 */
class StringTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Method to test isEmptyString().
	 *
	 * @return void
	 *
	 * @covers Windwalker\String\String::isEmptyString
	 */
	public function testIsEmpty()
	{
		$this->assertTrue(String::isEmpty(''));
		$this->assertFalse(String::isEmpty(0));
		$this->assertFalse(String::isEmpty(' '));
		$this->assertTrue(String::isEmpty(null));
		$this->assertFalse(String::isEmpty(true));
		$this->assertTrue(String::isEmpty(false));
	}

	/**
	 * Method to test isZero().
	 *
	 * @return void
	 *
	 * @covers Windwalker\String\String::isZero
	 * @TODO   Implement testIsZero().
	 */
	public function testIsZero()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test quote().
	 *
	 * @return void
	 *
	 * @covers Windwalker\String\String::quote
	 * @TODO   Implement testQuote().
	 */
	public function testQuote()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test backquote().
	 *
	 * @return void
	 *
	 * @covers Windwalker\String\String::backquote
	 * @TODO   Implement testBackquote().
	 */
	public function testBackquote()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test parseVariable().
	 *
	 * @return void
	 *
	 * @covers Windwalker\String\String::parseVariable
	 * @TODO   Implement testParseVariable().
	 */
	public function testParseVariable()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestIncrement()
	{
		return array(
			// Note: string, style, number, expected
			'First default increment' => array('title', null, 0, 'title (2)'),
			'Second default increment' => array('title(2)', null, 0, 'title(3)'),
			'First dash increment' => array('title', 'dash', 0, 'title-2'),
			'Second dash increment' => array('title-2', 'dash', 0, 'title-3'),
			'Set default increment' => array('title', null, 4, 'title (4)'),
			'Unknown style fallback to default' => array('title', 'foo', 0, 'title (2)'),
		);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string    @todo
	 * @param   string  $style     @todo
	 * @param   string  $number    @todo
	 * @param   string  $expected  @todo
	 *
	 * @return  void
	 *
	 * @covers        Windwalker\String\String::increment
	 * @dataProvider  seedTestIncrement
	 * @since         1.0
	 */
	public function testIncrement($string, $style, $number, $expected)
	{
		$this->assertEquals(
			$expected,
			String::increment($string, $style, $number)
		);
	}
}
