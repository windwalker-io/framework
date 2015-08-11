<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Dom\Test;

use Windwalker\Dom\Format\DomFormatter;
use Windwalker\Dom\Format\HtmlFormatter;
use Windwalker\Test\Helper\TestDomHelper;
use Windwalker\Test\TestCase\AbstractBaseTestCase;

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
			TestDomHelper::minify((string) $expected),
			TestDomHelper::minify((string) $actual),
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
			DomFormatter::format((string) $expected),
			DomFormatter::format((string) $actual),
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
			HtmlFormatter::format((string) $expected),
			HtmlFormatter::format((string) $actual),
			$message,
			$delta,
			$maxDepth,
			$canonicalize,
			$ignoreCase
		);
	}
}
