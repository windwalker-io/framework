<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Test\TestCase;

/**
 * The DomTestCase class.
 *
 * @since  2.0
 *
 * @deprecated  3.0  Use \Windwalker\Dom\Test\AbstractDomTestCase instead.
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
			static::minify($expected),
			static::minify($actual),
			$message,
			$delta,
			$maxDepth,
			$canonicalize,
			$ignoreCase
		);
	}

	/**
	 * A simple method to minify Dom and Html.
	 *
	 * Code from: http://stackoverflow.com/questions/6225351/how-to-minify-php-page-html-output
	 *
	 * @param string $buffer
	 *
	 * @return  mixed
	 */
	public static function minify($buffer)
	{
		$search = array(
			// Strip whitespaces after tags, except space
			'/\>[^\S ]+/s',

			// Strip whitespaces before tags, except space
			'/[^\S ]+\</s',

			// Shorten multiple whitespace sequences
			'/(\s)+/s'
		);

		$replace = array(
			'>',
			'<',
			'\\1'
		);

		$buffer = preg_replace($search, $replace, $buffer);

		return $buffer;
	}
}
