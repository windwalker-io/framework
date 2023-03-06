<?php declare(strict_types=1);
/**
 * @copyright  Copyright (C) 2019 LYRASOFT Source Matters, Inc.
 * @license    LGPL-2.0-or-later
 */

// phpcs:disable

namespace Windwalker\String\Test;

use Windwalker\String\StringNormalise;

/**
 * StringNormaliseTest
 *
 * @since  2.0
 */
class StringNormaliseTest extends \PHPUnit\Framework\TestCase
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
        return [
            // Note: string, expected
            ['FooBarABCDef', ['Foo', 'Bar', 'ABC', 'Def']],
            ['JFooBar', ['J', 'Foo', 'Bar']],
            ['J001FooBar002', ['J001', 'Foo', 'Bar002']],
            ['abcDef', ['abc', 'Def']],
            ['abc_defGhi_Jkl', ['abc_def', 'Ghi_Jkl']],
            ['ThisIsA_NASAAstronaut', ['This', 'Is', 'A_NASA', 'Astronaut']],
            ['JohnFitzgerald_Kennedy', ['John', 'Fitzgerald_Kennedy']],
        ];
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
        return [
            ['Foo Bar', 'FooBar'],
            ['foo Bar', 'fooBar'],
            ['Foobar', 'Foobar'],
            ['foobar', 'foobar'],
        ];
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
        return [
            ['FooBar', 'Foo Bar'],
            ['FooBar', 'Foo-Bar'],
            ['FooBar', 'Foo_Bar'],
            ['FooBar', 'foo bar'],
            ['FooBar', 'foo-bar'],
            ['FooBar', 'foo_bar'],
        ];
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
        return [
            ['Foo-Bar', 'Foo Bar'],
            ['Foo-Bar', 'Foo-Bar'],
            ['Foo-Bar', 'Foo_Bar'],
            ['foo-bar', 'foo bar'],
            ['foo-bar', 'foo-bar'],
            ['foo-bar', 'foo_bar'],
            ['foo-bar', 'foo   bar'],
            ['foo-bar', 'foo---bar'],
            ['foo-bar', 'foo___bar'],
        ];
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
        return [
            ['Foo Bar', 'Foo Bar'],
            ['Foo Bar', 'Foo-Bar'],
            ['Foo Bar', 'Foo_Bar'],
            ['foo bar', 'foo bar'],
            ['foo bar', 'foo-bar'],
            ['foo bar', 'foo_bar'],
            ['foo bar', 'foo   bar'],
            ['foo bar', 'foo---bar'],
            ['foo bar', 'foo___bar'],
        ];
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
        return [
            ['Foo_Bar', 'Foo Bar'],
            ['Foo_Bar', 'Foo-Bar'],
            ['Foo_Bar', 'Foo_Bar'],
            ['foo_bar', 'foo bar'],
            ['foo_bar', 'foo-bar'],
            ['foo_bar', 'foo_bar'],
            ['foo_bar', 'foo   bar'],
            ['foo_bar', 'foo---bar'],
            ['foo_bar', 'foo___bar'],
        ];
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
        return [
            ['myFooBar', 'My Foo Bar'],
            ['myFooBar', 'My Foo-Bar'],
            ['myFooBar', 'My Foo_Bar'],
            ['myFooBar', 'my foo bar'],
            ['myFooBar', 'my foo-bar'],
            ['myFooBar', 'my foo_bar'],
        ];
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
        return [
            ['foo_bar', 'Foo Bar'],
            ['foo_bar', 'Foo-Bar'],
            ['foo_bar', 'Foo_Bar'],
            ['foo_bar', 'foo bar'],
            ['foo_bar', 'foo-bar'],
            ['foo_bar', 'foo_bar'],
        ];
    }

    /**
     * Method to test StringNormalise::fromCamelCase(string, false).
     *
     * @param   string $expected The expected value from the method.
     * @param   string $input    The input value for the method.
     *
     * @return  void
     *
     * @covers        \Windwalker\String\StringNormalise::fromCamelcase
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
     * @param   string $input    The input value for the method.
     * @param   string $expected The expected value from the method.
     *
     * @return  void
     *
     * @covers        \Windwalker\String\StringNormalise::fromCamelcase
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
     * @param   string $expected The expected value from the method.
     * @param   string $input    The input value for the method.
     *
     * @return  void
     *
     * @covers        \Windwalker\String\StringNormalise::toCamelcase
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
     * @param   string $expected The expected value from the method.
     * @param   string $input    The input value for the method.
     *
     * @return  void
     *
     * @covers        \Windwalker\String\StringNormalise::toDashSeparated
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
     * @param   string $expected The expected value from the method.
     * @param   string $input    The input value for the method.
     *
     * @return  void
     *
     * @covers        \Windwalker\String\StringNormalise::toSpaceSeparated
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
     * @param   string $expected The expected value from the method.
     * @param   string $input    The input value for the method.
     *
     * @return  void
     *
     * @covers        \Windwalker\String\StringNormalise::toUnderscoreSeparated
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
     * @param   string $expected The expected value from the method.
     * @param   string $input    The input value for the method.
     *
     * @return  void
     *
     * @covers        \Windwalker\String\StringNormalise::toVariable
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
     * @param   string $expected The expected value from the method.
     * @param   string $input    The input value for the method.
     *
     * @return  void
     *
     * @covers        \Windwalker\String\StringNormalise::toKey
     * @dataProvider  seedTestToKey
     * @since         2.0
     */
    public function testToKey($expected, $input)
    {
        $this->assertEquals($expected, StringNormalise::toKey($input));
    }
}
