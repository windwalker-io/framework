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
class CompareHelperTest extends \PHPUnit_Framework_TestCase
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
		return array(
			// Equals
			array('foo', '=',   'foo', self::NOT_STRICT, true),
			array('foo', '=',   'foo', self::STRICT,     true),
			array('foo', '=',   'bar', self::NOT_STRICT, false),
			array(1,     '=',   '1',   self::NOT_STRICT, true),
			array(1,     '=',   '1',   self::STRICT,     false),
			array(1,     '=',   1,     self::STRICT,     true),
			array('foo', '==',  'foo', self::NOT_STRICT, true),
			array('foo', '==',  'foo', self::STRICT,     true),
			array(1,     '==',  '1',   self::NOT_STRICT, true),
			array(1,     '==',  '1',   self::STRICT,     false),
			array(1,     '==',  1,     self::STRICT,     true),
			array(1,     '===', '1',   self::STRICT,     false),
			array(1,     '===', '1',   self::NOT_STRICT, false),

			// Not equals
			array('foo', '!=',   'foo', self::NOT_STRICT, false),
			array('foo', '!=',   'foo', self::STRICT,     false),
			array('foo', '!=',   'bar', self::NOT_STRICT, true),
			array(1,     '!=',   '1',   self::NOT_STRICT, false),
			array(1,     '!=',   '1',   self::STRICT,     true),
			array(1,     '!=',   1,     self::STRICT,     false),
			array(1,     '!==',  '1',   self::NOT_STRICT, true),
			array(1,     '!==',  '1',   self::STRICT,     true),
			array(1,     '!==',  1,     self::STRICT,     false),

			// Gt
			array(1, '>', 1,   self::NOT_STRICT, false),
			array(1, '>', 2,   self::NOT_STRICT, false),
			array(2, '>', 1,   self::NOT_STRICT, true),
			array(1, 'gt', 1,   self::NOT_STRICT, false),
			array(1, 'gt', 2,   self::NOT_STRICT, false),
			array(2, 'gt', 1,   self::NOT_STRICT, true),

			// Gte
			array(1, '>=', 1,   self::NOT_STRICT, true),
			array(1, '>=', 2,   self::NOT_STRICT, false),
			array(2, '>=', 1,   self::NOT_STRICT, true),
			array(1, 'gte', 1,   self::NOT_STRICT, true),
			array(1, 'gte', 2,   self::NOT_STRICT, false),
			array(2, 'gte', 1,   self::NOT_STRICT, true),

			// Lt
			array(1, '<', 1,   self::NOT_STRICT, false),
			array(1, '<', 2,   self::NOT_STRICT, true),
			array(2, '<', 1,   self::NOT_STRICT, false),
			array(1, 'lt', 1,   self::NOT_STRICT, false),
			array(1, 'lt', 2,   self::NOT_STRICT, true),
			array(2, 'lt', 1,   self::NOT_STRICT, false),

			// Gte
			array(1, '<=', 1,   self::NOT_STRICT, true),
			array(1, '<=', 2,   self::NOT_STRICT, true),
			array(2, '<=', 1,   self::NOT_STRICT, false),
			array(1, 'lte', 1,   self::NOT_STRICT, true),
			array(1, 'lte', 2,   self::NOT_STRICT, true),
			array(2, 'lte', 1,   self::NOT_STRICT, false),

			// In
			array(1, 'in', array(1, 2, 3), self::NOT_STRICT, true),
			array('1', 'in', array(1, 2, 3), self::NOT_STRICT, true),
			array('1', 'in', array(1, 2, 3), self::STRICT, false),
			array(9, 'in', array(1, 2, 3), self::NOT_STRICT, false),

			// Not in
			array(1, 'nin', array(1, 2, 3), self::NOT_STRICT, false),
			array('1', 'nin', array(1, 2, 3), self::NOT_STRICT, false),
			array('1', 'nin', array(1, 2, 3), self::STRICT, true),
			array(9, 'nin', array(1, 2, 3), self::NOT_STRICT, true),
		);
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
