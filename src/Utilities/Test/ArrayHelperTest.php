<?php
/**
 * Part of ww4 project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT.
 * @license    Please see LICENSE file.
 */

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\ArrayHelper;

/**
 * The ArrayHelperTest class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ArrayHelperTest extends TestCase
{
    /**
     * testDef
     *
     * @param array|object $array
     * @param string       $key
     * @param string       $value
     * @param mixed        $expected
     *
     * @return  void
     *
     * @dataProvider providerTestDef
     */
    public function testDef($array, $key, $value, $expected)
    {
        self::assertEquals($expected, $return = ArrayHelper::def($array, $key, $value));

        if (is_object($array)) {
            self::assertSame($array, $return);
            self::assertEquals($array, $expected);
        }
    }

    /**
     * providerTestDef
     *
     * @return  array
     */
    public function providerTestDef()
    {
        return [
            [
                ['foo' => 'bar'],
                'foo',
                'yoo',
                ['foo' => 'bar'],
            ],
            [
                ['foo' => 'bar'],
                'baz',
                'goo',
                ['foo' => 'bar', 'baz' => 'goo'],
            ],
            [
                (object) ['foo' => 'bar'],
                'foo',
                'yoo',
                (object) ['foo' => 'bar'],
            ],
            [
                (object) ['foo' => 'bar'],
                'baz',
                'goo',
                (object) ['foo' => 'bar', 'baz' => 'goo'],
            ]
        ];
    }

    /**
     * testHas
     *
     * @return  void
     */
    public function testHas()
    {
        self::assertTrue(ArrayHelper::has(['foo' => 'bar'], 'foo'));
        self::assertFalse(ArrayHelper::has(['foo' => 'bar'], 'yoo'));
        self::assertTrue(ArrayHelper::has(['foo' => ['bar' => 'yoo']], 'foo.bar'));
    }

    public function testCollapse()
    {
        self::markTestIncomplete();
    }

    public function testFlatten()
    {
        self::markTestIncomplete();
    }

    public function testRemove()
    {
        self::markTestIncomplete();
    }

    public function testKeep()
    {
        self::markTestIncomplete();
    }

    public function testFind()
    {
        self::markTestIncomplete();
    }

    public function testFindFirst()
    {
        self::markTestIncomplete();
    }

    public function testGet()
    {
        self::markTestIncomplete();
    }

    public function testSet()
    {
        self::markTestIncomplete();
    }

    public function testPluck()
    {
        self::markTestIncomplete();
    }

    public function testTakeout()
    {
        self::markTestIncomplete();
    }

    public function testSort()
    {
        self::markTestIncomplete();
    }

    public function testSortRecursive()
    {
        self::markTestIncomplete();
    }

    public function testToArray()
    {
        self::markTestIncomplete();
    }

    public function testToObject()
    {
        self::markTestIncomplete();
    }

    public function testInvert()
    {
        self::markTestIncomplete();
    }

    public function testIsAssociative()
    {
        self::markTestIncomplete();
    }

    public function testGroup()
    {
        self::markTestIncomplete();
    }

    public function testUnique()
    {
        self::markTestIncomplete();
    }

    public function testMerge()
    {
        self::markTestIncomplete();
    }

    public function testDump()
    {
        self::markTestIncomplete();
    }

    public function testMatch()
    {
        self::markTestIncomplete();
    }

    public function testMap()
    {
        self::markTestIncomplete();
    }
}
