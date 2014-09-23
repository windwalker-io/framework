<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Application\Helper;

/**
 * Class ApplicationHelper
 *
 * @since {DEPLOY_VERSION}
 */
class ApplicationHelper
{
	/**
	 * Tests whether a string contains only 7bit ASCII bytes.
	 * You might use this to conditionally check whether a string
	 * needs handling as UTF-8 or not, potentially offering performance
	 * benefits by using the native PHP equivalent if it's just ASCII e.g.;
	 *
	 * @param   string  $string  The string to test.
	 *
	 * @return  boolean True if the string is all ASCII
	 */
	public static function isAscii($string)
	{
		// Search for any bytes which are outside the ASCII range...
		return (preg_match('/(?:[^\x00-\x7F])/', $string) !== 1);
	}
}

