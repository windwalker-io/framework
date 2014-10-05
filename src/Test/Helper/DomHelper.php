<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Test\Helper;

/**
 * The DomHelper class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class DomHelper
{
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

		$buffer = str_replace(array(' <', '> '), array('<', '>'), $buffer);

		return trim($buffer);
	}
}
