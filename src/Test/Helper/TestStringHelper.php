<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Test\Helper;

/**
 * The StringHelper class.
 * 
 * @since  2.0
 */
class TestStringHelper
{
	/**
	 * remove spaces
	 *
	 * See: http://stackoverflow.com/questions/3760816/remove-new-lines-from-string
	 * And: http://stackoverflow.com/questions/9558110/php-remove-line-break-or-cr-lf-with-no-success
	 *
	 * @param string $string
	 *
	 * @return  string
	 */
	public static function clean($string)
	{
		$string = preg_replace('/\s\s+/', ' ', $string);

		return trim(preg_replace('/\s+/', ' ', $string));
	}

	/**
	 * Convert CRLF to EOL
	 *
	 * @param string $string
	 *
	 * @return  string
	 */
	public static function removeCRLF($string)
	{
		return str_replace("\r\n", "\n", $string);
	}

	/**
	 * quote
	 *
	 * @param string       $string
	 * @param array|string $quote
	 *
	 * @return  string
	 */
	public static function quote($string, $quote = array('"', '"'))
	{
		$quote = (array) $quote;

		if (empty($quote[1]))
		{
			$quote[1] = $quote[0];
		}

		return $quote[0] . $string . $quote[1];
	}
}
