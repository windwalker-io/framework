<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Registry\Helper;

/**
 * Class RegistryHelper
 *
 * @since 1.0
 */
class RegistryHelper
{
	/**
	 * Method to determine if an array is an associative array.
	 *
	 * @param   array  $array  An array to test.
	 *
	 * @return  boolean  True if the array is an associative array.
	 */
	public static function isAssociativeArray($array)
	{
		if (is_array($array))
		{
			foreach (array_keys($array) as $k => $v)
			{
				if ($k !== $v)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * getValue
	 *
	 * @param array  $array
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return  null
	 */
	public static function getValue(array $array, $name, $default = null)
	{
		return isset($array[$name]) ? $array[$name] : $default;
	}
}
 