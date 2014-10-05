<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Test\Helper;

/**
 * The StringHelper class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class StringHelper
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
		return str_replace(PHP_EOL, "\n", $string);
	}
}
