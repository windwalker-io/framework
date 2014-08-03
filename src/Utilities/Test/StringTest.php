<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Utilities\Test;

use Windwalker\Utilities\String\String;

/**
 * Test class of String
 *
 * @since {DEPLOY_VERSION}
 */
class StringTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
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
	 * Method to test isEmptyString().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::isEmptyString
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
	 * @covers Windwalker\Utilities\String\String::isZero
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
	 * @covers Windwalker\Utilities\String\String::quote
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
	 * @covers Windwalker\Utilities\String\String::backquote
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
	 * @covers Windwalker\Utilities\String\String::parseVariable
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
	 * Method to test increment().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::increment
	 * @TODO   Implement testIncrement().
	 */
	public function testIncrement()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test is_ascii().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::is_ascii
	 * @TODO   Implement testIs_ascii().
	 */
	public function testIs_ascii()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test strpos().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::strpos
	 * @TODO   Implement testStrpos().
	 */
	public function testStrpos()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test strrpos().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::strrpos
	 * @TODO   Implement testStrrpos().
	 */
	public function testStrrpos()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test substr().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::substr
	 * @TODO   Implement testSubstr().
	 */
	public function testSubstr()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test strtolower().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::strtolower
	 * @TODO   Implement testStrtolower().
	 */
	public function testStrtolower()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test strtoupper().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::strtoupper
	 * @TODO   Implement testStrtoupper().
	 */
	public function testStrtoupper()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test strlen().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::strlen
	 * @TODO   Implement testStrlen().
	 */
	public function testStrlen()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test str_ireplace().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::str_ireplace
	 * @TODO   Implement testStr_ireplace().
	 */
	public function testStr_ireplace()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test str_split().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::str_split
	 * @TODO   Implement testStr_split().
	 */
	public function testStr_split()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test strcasecmp().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::strcasecmp
	 * @TODO   Implement testStrcasecmp().
	 */
	public function testStrcasecmp()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test strcmp().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::strcmp
	 * @TODO   Implement testStrcmp().
	 */
	public function testStrcmp()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test strcspn().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::strcspn
	 * @TODO   Implement testStrcspn().
	 */
	public function testStrcspn()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test stristr().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::stristr
	 * @TODO   Implement testStristr().
	 */
	public function testStristr()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test strrev().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::strrev
	 * @TODO   Implement testStrrev().
	 */
	public function testStrrev()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test strspn().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::strspn
	 * @TODO   Implement testStrspn().
	 */
	public function testStrspn()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test substr_replace().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::substr_replace
	 * @TODO   Implement testSubstr_replace().
	 */
	public function testSubstr_replace()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test ltrim().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::ltrim
	 * @TODO   Implement testLtrim().
	 */
	public function testLtrim()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test rtrim().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::rtrim
	 * @TODO   Implement testRtrim().
	 */
	public function testRtrim()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test trim().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::trim
	 * @TODO   Implement testTrim().
	 */
	public function testTrim()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test ucfirst().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::ucfirst
	 * @TODO   Implement testUcfirst().
	 */
	public function testUcfirst()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test ucwords().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::ucwords
	 * @TODO   Implement testUcwords().
	 */
	public function testUcwords()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test transcode().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::transcode
	 * @TODO   Implement testTranscode().
	 */
	public function testTranscode()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test valid().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::valid
	 * @TODO   Implement testValid().
	 */
	public function testValid()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test compliant().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Utilities\String\String::compliant
	 * @TODO   Implement testCompliant().
	 */
	public function testCompliant()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
