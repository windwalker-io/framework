<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Helper;

/**
 * Class StringHelper
 *
 * @since 1.0
 */
class StringHelper
{
	/**
	 * quote
	 *
	 * @param string $string
	 * @param string $quote
	 *
	 * @return  string
	 */
	public static function quote($string, $quote = "''")
	{
		if (empty($quote[1]))
		{
			$quote[1] = $quote[0];
		}

		return $quote[0] . $string . $quote[1];
	}

	/**
	 * backquote
	 *
	 * @param string $string
	 *
	 * @return  string
	 */
	public static function backquote($string)
	{
		return static::quote($string, '``');
	}

	/**
	 * parseVariable
	 *
	 * @param string $string
	 * @param array  $data
	 * @param array  $tags
	 *
	 * @return  string
	 */
	public static function parseVariable($string, $data = array(), $tags = array('{{', '}}'))
	{
		return preg_replace_callback(
			'/\{\{\s*(.+?)\s*\}\}/',
			function($match) use ($data)
			{
				return ArrayHelper::getByPath($data, $match[1]);
			},
			$string
		);
	}
}
