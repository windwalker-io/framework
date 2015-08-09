<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Test\TestCase;

use Windwalker\Dom\Format\DomFormatter;
use Windwalker\Test\Helper\DomHelper;

/**
 * The DomTestCase class.
 * 
 * @since  2.0
 */
class AbstractDomTestCase extends AbstractBaseTestCase
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
			DomHelper::minify((string) $expected),
			DomHelper::minify((string) $actual),
			$message,
			$delta,
			$maxDepth,
			$canonicalize,
			$ignoreCase
		);
	}

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
	public function assertDomFormatEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10,
		$canonicalize = FALSE, $ignoreCase = FALSE)
	{
		$this->assertEquals(
			DomFormatter::formatXml((string) $expected),
			DomFormatter::formatXml((string) $actual),
			$message,
			$delta,
			$maxDepth,
			$canonicalize,
			$ignoreCase
		);
	}

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
	public function assertHtmlFormatEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10,
		$canonicalize = FALSE, $ignoreCase = FALSE)
	{
		$this->assertEquals(
			DomFormatter::format((string) $expected),
			DomFormatter::format((string) $actual),
			$message,
			$delta,
			$maxDepth,
			$canonicalize,
			$ignoreCase
		);
	}
}
