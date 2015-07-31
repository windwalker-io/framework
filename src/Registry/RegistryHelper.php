<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Registry;

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

	/**
	 * Get data from array or object by path.
	 *
	 * Example: `RegistryHelper::getByPath($array, 'foo.bar.yoo')` equals to $array['foo']['bar']['yoo'].
	 *
	 * @param mixed  $data      An array or object to get value.
	 * @param mixed  $path      The key path.
	 * @param string $separator Separator of paths.
	 *
	 * @return  mixed Found value, null if not exists.
	 *
	 * @since   2.0
	 */
	public static function getByPath(array $data, $path, $separator = '.')
	{
		$nodes = static::getPathNodes($path, $separator);

		if (empty($nodes))
		{
			return null;
		}

		$dataTmp = $data;

		foreach ($nodes as $arg)
		{
			if (is_array($dataTmp) && isset($dataTmp[$arg]))
			{
				$dataTmp = $dataTmp[$arg];
			}
			else
			{
				return null;
			}
		}

		return $dataTmp;
	}

	/**
	 * setByPath
	 *
	 * @param mixed  &$data
	 * @param string $path
	 * @param mixed  $value
	 * @param string $separator
	 *
	 * @return  boolean
	 *
	 * @since   2.1
	 */
	public static function setByPath(array &$data, $path, $value, $separator = '.')
	{
		$nodes = static::getPathNodes($path, $separator);

		if (empty($nodes))
		{
			return false;
		}

		$dataTmp = &$data;

		foreach ($nodes as $node)
		{
			if (is_array($dataTmp))
			{
				if (empty($dataTmp[$node]))
				{
					$dataTmp[$node] = array();
				}

				$dataTmp = &$dataTmp[$node];
			}
			else
			{
				// If a node is value but path is not go to the end, we replace this value as a new store.
				// Then next node can insert new value to this store.
				$dataTmp = array();
			}
		}

		// Now, path go to the end, means we get latest node, set value to this node.
		$dataTmp = $value;

		return true;
	}

	/**
	 * Explode the registry path into an array and remove empty
	 * nodes that occur as a result of a double dot. ex: windwalker..test
	 * Finally, re-key the array so they are sequential.
	 *
	 * @param string $path
	 * @param string $separator
	 *
	 * @return  array
	 */
	public static function getPathNodes($path, $separator = '.')
	{
		return array_values(array_filter(explode($separator, $path), 'strlen'));
	}
}

