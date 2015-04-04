<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\String\Test;

use Windwalker\String\StringHelper;

/**
 * Test class of String
 *
 * @since 2.0
 */
class StringHelperTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Method to test isEmptyString().
	 *
	 * @return void
	 *
	 * @covers Windwalker\String\StringHelper::isEmptyString
	 */
	public function testIsEmpty()
	{
		$this->assertTrue(StringHelper::isEmpty(''));
		$this->assertFalse(StringHelper::isEmpty(0));
		$this->assertFalse(StringHelper::isEmpty(' '));
		$this->assertTrue(StringHelper::isEmpty(null));
		$this->assertFalse(StringHelper::isEmpty(true));
		$this->assertTrue(StringHelper::isEmpty(false));
	}

	/**
	 * Method to test isZero().
	 *
	 * @return void
	 *
	 * @covers Windwalker\String\StringHelper::isZero
	 */
	public function testIsZero()
	{
		$this->assertTrue(StringHelper::isZero(0));
		$this->assertTrue(StringHelper::isZero('0'));
		$this->assertFalse(StringHelper::isZero(''));
		$this->assertFalse(StringHelper::isZero(null));
		$this->assertFalse(StringHelper::isZero(true));
		$this->assertFalse(StringHelper::isZero(false));
	}

	/**
	 * Method to test quote().
	 *
	 * @return void
	 *
	 * @covers Windwalker\String\StringHelper::quote
	 */
	public function testQuote()
	{
		$this->assertEquals('"foo"', StringHelper::quote('foo', '"'));
		$this->assertEquals('"foo"', StringHelper::quote('foo', array('"', '"')));
		$this->assertEquals('[foo]', StringHelper::quote('foo', array('[', ']')));
	}

	/**
	 * Method to test backquote().
	 *
	 * @return void
	 *
	 * @covers Windwalker\String\StringHelper::backquote
	 */
	public function testBackquote()
	{
		$this->assertEquals('`foo`', StringHelper::backquote('foo'));
	}

	/**
	 * Method to test parseVariable().
	 *
	 * @return void
	 *
	 * @covers Windwalker\String\StringHelper::parseVariable
	 */
	public function testParseVariable()
	{
		$data['foo']['bar']['baz'] = 'Flower';

		$this->assertEquals('This is Flower', StringHelper::parseVariable('This is {{ foo.bar.baz }}', $data));
		$this->assertEquals('This is ', StringHelper::parseVariable('This is {{ foo.yoo }}', $data));
		$this->assertEquals('This is Flower', StringHelper::parseVariable('This is [ foo.bar.baz ]', $data, array('[', ']')));
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
	 * @param   string  $string    String to increment.
	 * @param   string  $style     Default of Dash.
	 * @param   string  $number    Number to increment.
	 * @param   string  $expected  Expected value.
	 *
	 * @return  void
	 *
	 * @covers        Windwalker\String\StringHelper::increment
	 * @dataProvider  seedTestIncrement
	 * @since         1.0
	 */
	public function testIncrement($string, $style, $number, $expected)
	{
		$this->assertEquals(
			$expected,
			StringHelper::increment($string, $style, $number)
		);
	}
}
