<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Compare\Test\Compare;

use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Compare\CompareHelper;

/**
 * Test class of CompareHelper
 *
 * @since 2.0
 */
class CompareHelperTest extends TestCase
{
    public const STRICT = true;

    public const NOT_STRICT = false;

    /**
     * getCompareData
     *
     * @return  array
     */
    public function getCompareData(): array
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

            // No operator
            [1, null, 1, self::NOT_STRICT, 0],
            [1, null, 2, self::NOT_STRICT, -1],
            [2, null, 1, self::NOT_STRICT, 1],
            [1, null, '1', self::NOT_STRICT, 0],
            [1, null, '1', self::STRICT, 0],
        ];
    }

    /**
     * Method to test compare().
     *
     * @param  mixed   $a
     * @param  string  $operator
     * @param  mixed   $b
     * @param  bool    $strict
     * @param  bool    $result
     *
     * @return void
     *
     * @dataProvider getCompareData
     */
    public function testCompare($a, $operator, $b, $strict, $result): void
    {
        self::assertEquals($result, CompareHelper::compare($a, $b, $operator, $strict));
    }
}
