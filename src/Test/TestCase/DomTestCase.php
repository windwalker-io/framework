<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Test\TestCase;

use Windwalker\Test\Helper\DomHelper;

/**
 * The DomTestCase class.
 * 
 * @since  2.0
 */
class DomTestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * Asserts that two variables are equal.
	 *
	 * @param  mixed   $expected
	 * @param  mixed   $actual
	 * @param  string  $message
	 * @param  float   $delta
	 * @param  integer $maxDepth
	 * @param  boolean $canonicalize
	 * @param  boolean $ignoreCase
	 */
	public function assertDomStringEqualsDomString($expected, $actual, $message = '', $delta = 0, $maxDepth = 10,
		$canonicalize = FALSE, $ignoreCase = FALSE)
	{
		$this->assertEquals(
			DomHelper::minify($expected),
			DomHelper::minify($actual),
			$message,
			$delta,
			$maxDepth,
			$canonicalize,
			$ignoreCase
		);
	}
}
