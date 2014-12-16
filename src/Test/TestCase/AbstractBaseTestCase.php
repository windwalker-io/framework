<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Test\TestCase;

use Windwalker\Test\Helper\StringHelper;

/**
 * The AbstractBaseTestCase class.
 * 
 * @since  2.0
 */
abstract class AbstractBaseTestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * assertStringDataEquals
	 *
	 * @param string $expected
	 * @param string $actual
	 * @param string $message
	 * @param int    $delta
	 * @param int    $maxDepth
	 * @param bool   $canonicalize
	 * @param bool   $ignoreCase
	 *
	 * @return  void
	 */
	public function assertStringDataEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
	{
		$this->assertEquals(
			StringHelper::clean($expected),
			StringHelper::clean($actual),
			$message,
			$delta,
			$maxDepth,
			$canonicalize,
			$ignoreCase
		);
	}

	/**
	 * assertStringDataEquals
	 *
	 * @param string $expected
	 * @param string $actual
	 * @param string $message
	 * @param int    $delta
	 * @param int    $maxDepth
	 * @param bool   $canonicalize
	 * @param bool   $ignoreCase
	 *
	 * @return  void
	 */
	public function assertStringSafeEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
	{
		$this->assertEquals(
			trim(StringHelper::removeCRLF($expected)),
			trim(StringHelper::removeCRLF($actual)),
			$message,
			$delta,
			$maxDepth,
			$canonicalize,
			$ignoreCase
		);
	}
}
