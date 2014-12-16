<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Registry\Helper;

/**
 * Class RegistryHelper
 *
 * @since 2.0
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

	/**
	 * Utility function to map an array to a stdClass object.
	 *
	 * @param   array   $array  The array to map.
	 * @param   string  $class  Name of the class to create
	 *
	 * @return  object   The object mapped from the given array
	 *
	 * @since   2.0
	 */
	public static function toObject($array, $class = 'stdClass')
	{
		$obj = new $class;

		foreach ($array as $k => $v)
		{
			if (is_array($v))
			{
				$obj->$k = self::toObject($v, $class);
			}
			else
			{
				$obj->$k = $v;
			}
		}

		return $obj;
	}
}

