<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form;

/**
 * The FormHelper class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class FormHelper
{
	/**
	 * encode
	 *
	 * @param string $html
	 *
	 * @return  string
	 */
	public static function encode($html)
	{
		return htmlentities($html);
	}

	/**
	 * Dump to on dimension array.
	 *
	 * @param array  $data      The data to convert.
	 * @param string $separator The key separator.
	 *
	 * @return  string[] Dumped array.
	 */
	public static function flatten($data, $separator = '.')
	{
		$array = array();

		static::toFlatten($separator, $data, $array);

		return $array;
	}

	/**
	 * Method to recursively convert data to one dimension array.
	 *
	 * @param string        $separator The key separator.
	 * @param array|object  $data      Data source of this scope.
	 * @param array         &$array    The result array, it is pass by reference.
	 * @param string        $prefix    Last level key prefix.
	 *
	 * @return  void
	 */
	protected static function toFlatten($separator = '_', $data = null, &$array = array(), $prefix = '')
	{
		$data = (array) $data;

		foreach ($data as $k => $v)
		{
			$key = $prefix ? $prefix . $separator . $k : $k;

			if (is_object($v) || is_array($v))
			{
				static::toFlatten($separator, $v, $array, $key);
			}
			else
			{
				$array[$key] = $v;
			}
		}
	}

	/**
	 * Get data from array or object by path.
	 *
	 * Example: `ArrayHelper::getByPath($array, 'foo.bar.yoo')` equals to $array['foo']['bar']['yoo'].
	 *
	 * @param mixed  $data      An array or object to get value.
	 * @param mixed  $paths     The key path.
	 * @param string $separator Separator of paths.
	 *
	 * @return  mixed Found value, null if not exists.
	 */
	public static function getByPath($data, $paths, $separator = '.')
	{
		if (empty($paths))
		{
			return null;
		}

		$args = is_array($paths) ? $paths : explode($separator, $paths);

		$dataTmp = $data;

		foreach ($args as $arg)
		{
			if (is_object($dataTmp) && !empty($dataTmp->$arg))
			{
				$dataTmp = $dataTmp->$arg;
			}
			elseif (is_array($dataTmp) && !empty($dataTmp[$arg]))
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
}
