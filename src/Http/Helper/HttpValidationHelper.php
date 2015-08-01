<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Http\Helper;

/**
 * The HttpValidationHelper class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class HttpValidationHelper
{
	/**
	 * allToArray
	 *
	 * @param mixed $value
	 *
	 * @return  array
	 */
	public static function allToArray($value)
	{
		if ($value instanceof \Traversable)
		{
			$value = iterator_to_array($value);
		}

		if (is_object($value))
		{
			$value = get_object_vars($value);
		}

		return (array) $value;
	}

	/**
	 * arrayOnlyContainsString
	 *
	 * @param array $array
	 *
	 * @return  bool
	 */
	public static function arrayOnlyContainsString(array $array)
	{
		foreach ($array as $value)
		{
			if (!is_string($value))
			{
				return false;
			}
		}

		return true;
	}
}
