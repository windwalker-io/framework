<?php
/**
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Source Matters, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\String\Tests;

use Windwalker\String\StringNormalise;

/**
 * StringNormaliseTest
 *
 * @since  2.0
 */
class StringNormaliseTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Method to seed data to testFromCamelCase.
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestFromCamelCase()
	{
		return array(
			// Note: string, expected
			array('FooBarABCDef', array('Foo', 'Bar', 'ABC', 'Def')),
			array('JFooBar', array('J', 'Foo', 'Bar')),
			array('J001FooBar002', array('J001', 'Foo', 'Bar002')),
			array('abcDef', array('abc', 'Def')),
			array('abc_defGhi_Jkl', array('abc_def', 'Ghi_Jkl')),
			array('ThisIsA_NASAAstronaut', array('This', 'Is', 'A_NASA', 'Astronaut')),
			array('JohnFitzgerald_Kennedy', array('John', 'Fitzgerald_Kennedy')),
		);
	}

	/**
	 * Method to seed data to testFromCamelCase.
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestFromCamelCase_nongrouped()
	{
		return array(
			array('Foo Bar', 'FooBar'),
			array('foo Bar', 'fooBar'),
			array('Foobar', 'Foobar'),
			array('foobar', 'foobar')
		);
	}

	/**
	 * Method to seed data to testToCamelCase.
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestToCamelCase()
	{
		return array(
			array('FooBar', 'Foo Bar'),
			array('FooBar', 'Foo-Bar'),
			array('FooBar', 'Foo_Bar'),
			array('FooBar', 'foo bar'),
			array('FooBar', 'foo-bar'),
			array('FooBar', 'foo_bar'),
		);
	}

	/**
	 * Method to seed data to testToDashSeparated.
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestToDashSeparated()
	{
		return array(
			array('Foo-Bar', 'Foo Bar'),
			array('Foo-Bar', 'Foo-Bar'),
			array('Foo-Bar', 'Foo_Bar'),
			array('foo-bar', 'foo bar'),
			array('foo-bar', 'foo-bar'),
			array('foo-bar', 'foo_bar'),
			array('foo-bar', 'foo   bar'),
			array('foo-bar', 'foo---bar'),
			array('foo-bar', 'foo___bar'),
		);
	}

	/**
	 * Method to seed data to testToSpaceSeparated.
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestToSpaceSeparated()
	{
		return array(
			array('Foo Bar', 'Foo Bar'),
			array('Foo Bar', 'Foo-Bar'),
			array('Foo Bar', 'Foo_Bar'),
			array('foo bar', 'foo bar'),
			array('foo bar', 'foo-bar'),
			array('foo bar', 'foo_bar'),
			array('foo bar', 'foo   bar'),
			array('foo bar', 'foo---bar'),
			array('foo bar', 'foo___bar'),
		);
	}

	/**
	 * Method to seed data to testToUnderscoreSeparated.
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestToUnderscoreSeparated()
	{
		return array(
			array('Foo_Bar', 'Foo Bar'),
			array('Foo_Bar', 'Foo-Bar'),
			array('Foo_Bar', 'Foo_Bar'),
			array('foo_bar', 'foo bar'),
			array('foo_bar', 'foo-bar'),
			array('foo_bar', 'foo_bar'),
			array('foo_bar', 'foo   bar'),
			array('foo_bar', 'foo---bar'),
			array('foo_bar', 'foo___bar'),
		);
	}

	/**
	 * Method to seed data to testToVariable.
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestToVariable()
	{
		return array(
			array('myFooBar', 'My Foo Bar'),
			array('myFooBar', 'My Foo-Bar'),
			array('myFooBar', 'My Foo_Bar'),
			array('myFooBar', 'my foo bar'),
			array('myFooBar', 'my foo-bar'),
			array('myFooBar', 'my foo_bar'),
		);
	}

	/**
	 * Method to seed data to testToKey.
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestToKey()
	{
		return array(
			array('foo_bar', 'Foo Bar'),
			array('foo_bar', 'Foo-Bar'),
			array('foo_bar', 'Foo_Bar'),
			array('foo_bar', 'foo bar'),
			array('foo_bar', 'foo-bar'),
			array('foo_bar', 'foo_bar'),
		);
	}

	/**
	 * Method to test StringNormalise::fromCamelCase(string, false).
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @covers        Windwalker\String\StringNormalise::fromCamelcase
	 * @dataProvider  seedTestFromCamelCase_nongrouped
	 * @since         2.0
	 */
	public function testFromCamelCase_nongrouped($expected, $input)
	{
		$this->assertEquals($expected, StringNormalise::fromCamelcase($input));
	}

	/**
	 * Method to test StringNormalise::fromCamelCase(string, true).
	 *
	 * @param   string  $input     The input value for the method.
	 * @param   string  $expected  The expected value from the method.
	 *
	 * @return  void
	 *
	 * @covers        Windwalker\String\StringNormalise::fromCamelcase
	 * @dataProvider  seedTestFromCamelCase
	 * @since         2.0
	 */
	public function testFromCamelCase_grouped($input, $expected)
	{
		$this->assertEquals($expected, StringNormalise::fromCamelcase($input, true));
	}

	/**
	 * Method to test StringNormalise::toCamelCase().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @covers        Windwalker\String\StringNormalise::toCamelcase
	 * @dataProvider  seedTestToCamelCase
	 * @since         2.0
	 */
	public function testToCamelCase($expected, $input)
	{
		$this->assertEquals($expected, StringNormalise::toCamelcase($input));
	}

	/**
	 * Method to test StringNormalise::toDashSeparated().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @covers        Windwalker\String\StringNormalise::toDashSeparated
	 * @dataProvider  seedTestToDashSeparated
	 * @since         2.0
	 */
	public function testToDashSeparated($expected, $input)
	{
		$this->assertEquals($expected, StringNormalise::toDashSeparated($input));
	}

	/**
	 * Method to test StringNormalise::toSpaceSeparated().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @covers        Windwalker\String\StringNormalise::toSpaceSeparated
	 * @dataProvider  seedTestToSpaceSeparated
	 * @since         2.0
	 */
	public function testToSpaceSeparated($expected, $input)
	{
		$this->assertEquals($expected, StringNormalise::toSpaceSeparated($input));
	}

	/**
	 * Method to test StringNormalise::toUnderscoreSeparated().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @covers        Windwalker\String\StringNormalise::toUnderscoreSeparated
	 * @dataProvider  seedTestToUnderscoreSeparated
	 * @since         2.0
	 */
	public function testToUnderscoreSeparated($expected, $input)
	{
		$this->assertEquals($expected, StringNormalise::toUnderscoreSeparated($input));
	}

	/**
	 * Method to test StringNormalise::toVariable().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @covers        Windwalker\String\StringNormalise::toVariable
	 * @dataProvider  seedTestToVariable
	 * @since         2.0
	 */
	public function testToVariable($expected, $input)
	{
		$this->assertEquals($expected, StringNormalise::toVariable($input));
	}

	/**
	 * Method to test StringNormalise::toKey().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @covers        Windwalker\String\StringNormalise::toKey
	 * @dataProvider  seedTestToKey
	 * @since         2.0
	 */
	public function testToKey($expected, $input)
	{
		$this->assertEquals($expected, StringNormalise::toKey($input));
	}
}
