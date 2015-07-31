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
	 * Load the contents of a file into the registry
	 *
	 * @param   string  $file     Path to file to load
	 * @param   string  $format   Format of the file [optional: defaults to JSON]
	 * @param   array   $options  Options used by the formatter
	 *
	 * @return  static  Return this object to support chaining.
	 *
	 * @since   2.1
	 */
	public static function loadFile($file, $format = Format::JSON, $options = array())
	{
		if (strtolower($format) == Format::PHP)
		{
			$data = include $file;
		}
		else
		{
			$data = file_get_contents($file);
		}

		return static::loadString($data, $format, $options);
	}

	/**
	 * Load a string into the registry
	 *
	 * @param   string  $data     String to load into the registry
	 * @param   string  $format   Format of the string
	 * @param   array   $options  Options used by the formatter
	 *
	 * @return  static  Return this object to support chaining.
	 *
	 * @since   2.1
	 */
	public static function loadString($data, $format = Format::JSON, $options = array())
	{
		// Load a string into the given namespace [or default namespace if not given]
		$class = static::getFormatClass($format);

		return $class::stringToStruct($data, $options);
	}

	/**
	 * Get a namespace in a given string format
	 *
	 * @param   array|object  $data     The structure data to convert to markup string.
	 * @param   string        $format   Format to return the string in
	 * @param   mixed         $options  Parameters used by the formatter, see formatters for more info
	 *
	 * @return  string  Namespace in string format
	 *
	 * @since   2.1
	 */
	public static function toString($data, $format = Format::JSON, $options = array())
	{
		$class = static::getFormatClass($format);

		return $class::structToString($data, $options);
	}

	/**
	 * getFormatClass
	 *
	 * @param string $format
	 *
	 * @return  string|\Windwalker\Registry\Format\FormatInterface
	 *
	 * @throws  \DomainException
	 *
	 * @since   2.1
	 */
	protected static function getFormatClass($format)
	{
		// Return a namespace in a given format
		$class = sprintf('%s\Format\%sFormat', __NAMESPACE__, ucfirst(strtolower($format)));

		if (!class_exists($class))
		{
			throw new \DomainException(sprintf('Registry format: %s not supported. Class: %s not found.', $format, $class));
		}

		return $class;
	}

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
	 * @return  mixed
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
		$object = new $class;

		foreach ($array as $k => $v)
		{
			if (is_array($v))
			{
				$object->$k = static::toObject($v, $class);
			}
			else
			{
				$object->$k = $v;
			}
		}

		return $object;
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
	 * @since   2.1
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

	/**
	 * Method to recursively convert data to one dimension array.
	 *
	 * @param   array|object  $array      The array or object to convert.
	 * @param   string        $separator  The key separator.
	 * @param   string        $prefix     Last level key prefix.
	 *
	 * @return  array
	 */
	public static function flatten($array, $separator = '.', $prefix = '')
	{
		if ($array instanceof \Traversable)
		{
			$array = iterator_to_array($array);
		}
		elseif (is_object($array))
		{
			$array = get_object_vars($array);
		}

		foreach ($array as $k => $v)
		{
			$key = $prefix ? $prefix . $separator . $k : $k;

			if (is_object($v) || is_array($v))
			{
				$array = array_merge($array, static::flatten($v, $separator, $key));
			}
			else
			{
				$array[$key] = $v;
			}
		}

		return $array;
	}
}

