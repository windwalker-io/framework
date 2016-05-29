<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Http\Test\Helper;

use Windwalker\Http\Helper\HeaderHelper;

/**
 * Test class of HeaderHelper
 *
 * @since {DEPLOY_VERSION}
 */
class HerderHelperTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Method to test getValue().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\Helper\HeaderHelper::getValue
	 */
	public function testGetValue()
	{
		$headers = array(
			'x-foo' => 'baz',
			'X-Flower' => array(
				'sakura',
				'olive'
			)
		);

		$this->assertEquals('baz', HeaderHelper::getValue($headers, 'x-foo'));
		$this->assertEquals('sakura, olive', HeaderHelper::getValue($headers, 'x-flower'));
		$this->assertEquals('default', HeaderHelper::getValue($headers, 'x-car', 'default'));
	}

	/**
	 * Method to test isValidName().
	 *
	 * @param string $string
	 * @param string $expected
	 *
	 * @covers       Windwalker\Http\Helper\HeaderHelper::isValidName
	 * @dataProvider isValidName_Provider
	 */
	public function testIsValidName($string, $expected)
	{
		$this->assertEquals($expected, HeaderHelper::isValidName($string), 'Fail assert this string: ' . $string);
	}

	/**
	 * isValidName_Provider
	 *
	 * @return  array
	 */
	public function isValidName_Provider()
	{
		return array(
			array('Sakura', true),
			array('X-Flower-Sakura', true),
			array('x-flower-sakura', true),
			array('x-flower - sakura', false),
			array("X-Flower\xFF-Sakura", false),
			array("X-Flower\x7F-Sakura", false),
			array("X-Flower\n -Sakura", false),
			array("X-Flower\n\r -Sakura", false),
			array("X-Flower\r\n -Sakura", false),
			array("X-Flower \r\n -Sakura", false),
			array("X-Flower \r\n-Sakura", false),
			array("X-Flower \r\r\n -Sakura", false),
			array("X-Flower ; -Sakura", false),
			array("X-Flower {O:\"Class\"} -Sakura", false),
		);
	}

	/**
	 * Method to test filter().
	 *
	 * @param string  $string
	 * @param string  $expected
	 * @param integer $num
	 *
	 * @covers       Windwalker\Http\Helper\HeaderHelper::filter
	 * @dataProvider filter_Provider
	 */
	public function testFilter($string, $expected, $num)
	{
		$this->assertEquals($expected, HeaderHelper::filter($string), 'Result should be: ' . str_replace(array("\t", "\n", "\r"), array("\\t", "\\n", "\\r"), HeaderHelper::filter($string)) . ' - #' . $num);
	}

	/**
	 * filter_Provider
	 *
	 * @return  array
	 */
	public function filter_Provider()
	{
		return array(
			array("I can do this all day", "I can do this all day", 1),
			array("I can do this\n all day", "I can do this all day", 2),
			array("I can do this\r all day", "I can do this all day", 3),
			array("I can do this\r\n all day", "I can do this\r\n all day", 4),
			array("I can do this\n\r all day", "I can do this all day", 5),
			array("I can do this \n\r all day", "I can do this  all day", 6),
			array("I can do this \n\rall day", "I can do this all day", 7),
			array("I can do this \n\n all day", "I can do this  all day", 8),
			array("I can do this \r\r all day", "I can do this  all day", 9),
			array("I can do this \r\r\n all day", "I can do this \r\n all day", 10),
			array("I can do this \r\n\n all day", "I can do this  all day", 11),
			array("I can do this \n\n\r all day", "I can do this  all day", 12),
			array("I can do this \n\r\r all day", "I can do this  all day", 13),
			array("I can do this \r\n\n\r\n all day", "I can do this \r\n all day", 14),
		);
	}

	/**
	 * Method to test isValidValue().
	 *
	 * @param string  $string
	 * @param string  $expected
	 * @param integer $num
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\Helper\HeaderHelper::isValidValue
	 * @dataProvider  isValidValue_Provider
	 */
	public function testIsValidValue($string, $expected, $num)
	{
		$this->assertEquals($expected, HeaderHelper::isValidValue($string), str_replace(array("\t", "\n", "\r"), array("\\t", "\\n", "\\r"), $string) . ' assert fail - #' . $num);
	}

	/**
	 * isValidValue_Provider
	 *
	 * @return  array
	 */
	public function isValidValue_Provider()
	{
		return array(
			array("I can do this all day", true, 1),
			array("I can do this\n all day", false, 2),
			array("I can do this\r all day", false, 3),
			array("I can do this\r\n all day", true, 4),
			array("I can do this\n\r all day", false, 5),
			array("I can do this \n\r all day", false, 6),
			array("I can do this \n\rall day", false, 7),
			array("I can do this \n\n all day", false, 8),
			array("I can do this \r\r all day", false, 9),
			array("I can do this \r\r\n all day", false, 10),
			array("I can do this \r\n\n all day", false, 11),
			array("I can do this \n\n\r all day", false, 12),
			array("I can do this \n\r\r all day", false, 13),
			array("I can do this \r\n\n\r\n all day", false, 14),
		);
	}

	/**
	 * Method to test allToArray().
	 *
	 * @param mixed $source
	 * @param array $expected
	 *
	 * @covers        Windwalker\Http\Helper\HeaderHelper::allToArray
	 * @dataProvider  allToArray_Provider
	 */
	public function testAllToArray($source, $expected)
	{
		$this->assertEquals($expected, HeaderHelper::allToArray($source));
	}

	/**
	 * allToArray_Provider
	 *
	 * @return  array
	 */
	public function allToArray_Provider()
	{
		return array(
			array(
				array('A', 'B', 'C'),
				array('A', 'B', 'C')
			),
			array(
				(object) array('A', 'B', 'C'),
				array('A', 'B', 'C')
			),
			array(
				new \ArrayIterator(array('A', 'B', 'C')),
				array('A', 'B', 'C')
			),
			array(
				'A',
				array('A')
			)
		);
	}

	/**
	 * Method to test arrayOnlyContainsString().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\Helper\HeaderHelper::arrayOnlyContainsString
	 */
	public function testArrayOnlyContainsString()
	{
		$actual = array('A', 'B', 'C');

		$this->assertTrue(HeaderHelper::arrayOnlyContainsString($actual));

		$actual = array('A', 'B', 3);

		$this->assertTrue(HeaderHelper::arrayOnlyContainsString($actual));

		$actual = array('A', 'B', new \stdClass);

		$this->assertFalse(HeaderHelper::arrayOnlyContainsString($actual));
	}

	/**
	 * Method to test toHeaderLine().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\Helper\HeaderHelper::toHeaderLine
	 */
	public function testToHeaderLine()
	{
		$headers = array(
			'x-foo' => 'baz',
			'X-Flower' => array(
				'sakura',
				'olive'
			)
		);

		$this->assertEquals(array('X-Foo: baz', 'X-Flower: sakura,olive'), HeaderHelper::toHeaderLine($headers));
	}

	/**
	 * Method to test normalizeHeaderName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\Helper\HeaderHelper::normalizeHeaderName
	 */
	public function testNormalizeHeaderName()
	{
		$this->assertEquals('X-Foo', HeaderHelper::normalizeHeaderName('X-Foo'));
		$this->assertEquals('X-Foo', HeaderHelper::normalizeHeaderName('x-foo'));
		$this->assertEquals('X-Foo', HeaderHelper::normalizeHeaderName('X Foo'));
		$this->assertEquals('X-Foo', HeaderHelper::normalizeHeaderName('X foo'));
	}
}
