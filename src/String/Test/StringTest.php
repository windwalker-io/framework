<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\String\Test;

use Windwalker\String\String;

/**
 * Test class of String
 *
 * @since 2.0
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
	 */
	public function testIsZero()
	{
		$this->assertTrue(String::isZero(0));
		$this->assertTrue(String::isZero('0'));
		$this->assertFalse(String::isZero(''));
		$this->assertFalse(String::isZero(null));
		$this->assertFalse(String::isZero(true));
		$this->assertFalse(String::isZero(false));
	}

	/**
	 * Method to test quote().
	 *
	 * @return void
	 *
	 * @covers Windwalker\String\String::quote
	 */
	public function testQuote()
	{
		$this->assertEquals('"foo"', String::quote('foo', '"'));
		$this->assertEquals('"foo"', String::quote('foo', array('"', '"')));
		$this->assertEquals('[foo]', String::quote('foo', array('[', ']')));
	}

	/**
	 * Method to test backquote().
	 *
	 * @return void
	 *
	 * @covers Windwalker\String\String::backquote
	 */
	public function testBackquote()
	{
		$this->assertEquals('`foo`', String::backquote('foo'));
	}

	/**
	 * Method to test parseVariable().
	 *
	 * @return void
	 *
	 * @covers Windwalker\String\String::parseVariable
	 */
	public function testParseVariable()
	{
		$data['foo']['bar']['baz'] = 'Flower';

		$this->assertEquals('This is Flower', String::parseVariable('This is {{ foo.bar.baz }}', $data));
		$this->assertEquals('This is ', String::parseVariable('This is {{ foo.yoo }}', $data));
		$this->assertEquals('This is Flower', String::parseVariable('This is [ foo.bar.baz ]', $data, array('[', ']')));
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
