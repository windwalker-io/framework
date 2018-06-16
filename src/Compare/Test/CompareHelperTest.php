<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Compare\Test;

use Windwalker\Compare\CompareHelper;

/**
 * Test class of CompareHelper
 *
 * @since 2.0
 */
class CompareHelperTest extends \PHPUnit\Framework\TestCase
{
    const STRICT = true;

    const NOT_STRICT = false;

    /**
     * getCompareData
     *
     * @return  array
     */
    public function getCompareData()
    {
        return [
            // Equals
            ['foo', '=', 'foo', self::NOT_STRICT, true],
            ['foo', '=', 'foo', self::STRICT, true],
            ['foo', '=', 'bar', self::NOT_STRICT, false],
            [1, '=', '1', self::NOT_STRICT, true],
            [1, '=', '1', self::STRICT, false],
            [1, '=', 1, self::STRICT, true],
            ['foo', '==', 'foo', self::NOT_STRICT, true],
            ['foo', '==', 'foo', self::STRICT, true],
            [1, '==', '1', self::NOT_STRICT, true],
            [1, '==', '1', self::STRICT, false],
            [1, '==', 1, self::STRICT, true],
            [1, '===', '1', self::STRICT, false],
            [1, '===', '1', self::NOT_STRICT, false],

            // Not equals
            ['foo', '!=', 'foo', self::NOT_STRICT, false],
            ['foo', '!=', 'foo', self::STRICT, false],
            ['foo', '!=', 'bar', self::NOT_STRICT, true],
            [1, '!=', '1', self::NOT_STRICT, false],
            [1, '!=', '1', self::STRICT, true],
            [1, '!=', 1, self::STRICT, false],
            [1, '!==', '1', self::NOT_STRICT, true],
            [1, '!==', '1', self::STRICT, true],
            [1, '!==', 1, self::STRICT, false],

            // Gt
            [1, '>', 1, self::NOT_STRICT, false],
            [1, '>', 2, self::NOT_STRICT, false],
            [2, '>', 1, self::NOT_STRICT, true],
            [1, 'gt', 1, self::NOT_STRICT, false],
            [1, 'gt', 2, self::NOT_STRICT, false],
            [2, 'gt', 1, self::NOT_STRICT, true],

            // Gte
            [1, '>=', 1, self::NOT_STRICT, true],
            [1, '>=', 2, self::NOT_STRICT, false],
            [2, '>=', 1, self::NOT_STRICT, true],
            [1, 'gte', 1, self::NOT_STRICT, true],
            [1, 'gte', 2, self::NOT_STRICT, false],
            [2, 'gte', 1, self::NOT_STRICT, true],

            // Lt
            [1, '<', 1, self::NOT_STRICT, false],
            [1, '<', 2, self::NOT_STRICT, true],
            [2, '<', 1, self::NOT_STRICT, false],
            [1, 'lt', 1, self::NOT_STRICT, false],
            [1, 'lt', 2, self::NOT_STRICT, true],
            [2, 'lt', 1, self::NOT_STRICT, false],

            // Gte
            [1, '<=', 1, self::NOT_STRICT, true],
            [1, '<=', 2, self::NOT_STRICT, true],
            [2, '<=', 1, self::NOT_STRICT, false],
            [1, 'lte', 1, self::NOT_STRICT, true],
            [1, 'lte', 2, self::NOT_STRICT, true],
            [2, 'lte', 1, self::NOT_STRICT, false],

            // In
            [1, 'in', [1, 2, 3], self::NOT_STRICT, true],
            ['1', 'in', [1, 2, 3], self::NOT_STRICT, true],
            ['1', 'in', [1, 2, 3], self::STRICT, false],
            [9, 'in', [1, 2, 3], self::NOT_STRICT, false],

            // Not in
            [1, 'nin', [1, 2, 3], self::NOT_STRICT, false],
            ['1', 'nin', [1, 2, 3], self::NOT_STRICT, false],
            ['1', 'nin', [1, 2, 3], self::STRICT, true],
            [9, 'nin', [1, 2, 3], self::NOT_STRICT, true],
        ];
    }

    /**
     * Method to test compare().
     *
     * @param mixed  $compare1
     * @param string $operator
     * @param mixed  $compare2
     * @param bool   $strict
     * @param bool   $result
     *
     * @return void
     *
     * @dataProvider getCompareData
     *
     * @covers       Windwalker\Compare\CompareHelper::compare
     */
    public function testCompare($compare1, $operator, $compare2, $strict, $result)
    {
        $this->assertEquals($result, CompareHelper::compare($compare1, $operator, $compare2, $strict));
    }
}
