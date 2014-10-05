<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Utilities;

use Windwalker\String\Utf8String;

/**
 * The ArrayHelper class.
 *
 * @since  {DEPLOY_VERSION}
 */
abstract class ArrayHelper
{
	/**
	 * Function to convert array to integer values
	 *
	 * @param   array  $array    The source array to convert
	 * @param   mixed  $default  A default value (int|array) to assign if $array is not an array
	 *
	 * @return  array The converted array
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public static function toInteger($array, $default = null)
	{
		if (is_array($array))
		{
			$array = array_map('intval', $array);
		}
		else
		{
			if ($default === null)
			{
				$array = array();
			}
			elseif (is_array($default))
			{
				$array = self::toInteger($default, null);
			}
			else
			{
				$array = array((int) $default);
			}
		}

		return $array;
	}

	/**
	 * Utility function to map an array to a stdClass object.
	 *
	 * @param   array   $array  The array to map.
	 * @param   string  $class  Name of the class to create
	 *
	 * @return  object   The object mapped from the given array
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public static function toObject(array $array, $class = 'stdClass')
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
	 * Utility function to map an array to a string.
	 *
	 * @param   array    $array         The array to map.
	 * @param   string   $inner_glue    The glue (optional, defaults to '=') between the key and the value.
	 * @param   string   $outer_glue    The glue (optional, defaults to ' ') between array elements.
	 * @param   boolean  $keepOuterKey  True if final key should be kept.
	 *
	 * @return  string   The string mapped from the given array
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public static function toString(array $array, $inner_glue = '=', $outer_glue = ' ', $keepOuterKey = false)
	{
		$output = array();

		foreach ($array as $key => $item)
		{
			if (is_array($item))
			{
				if ($keepOuterKey)
				{
					$output[] = $key;
				}

				// This is value is an array, go and do it again!
				$output[] = self::toString($item, $inner_glue, $outer_glue, $keepOuterKey);
			}
			else
			{
				$output[] = $key . $inner_glue . '"' . $item . '"';
			}
		}

		return implode($outer_glue, $output);
	}

	/**
	 * Utility function to map an object to an array
	 *
	 * @param   object   $p_obj    The source object
	 * @param   boolean  $recurse  True to recurse through multi-level objects
	 * @param   string   $regex    An optional regular expression to match on field names
	 *
	 * @return  array    The array mapped from the given object
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public static function fromObject($p_obj, $recurse = true, $regex = null)
	{
		if (is_object($p_obj))
		{
			return self::arrayFromObject($p_obj, $recurse, $regex);
		}
		else
		{
			return null;
		}
	}

	/**
	 * Utility function to map an object or array to an array
	 *
	 * @param   mixed    $item     The source object or array
	 * @param   boolean  $recurse  True to recurse through multi-level objects
	 * @param   string   $regex    An optional regular expression to match on field names
	 *
	 * @return  array  The array mapped from the given object
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	private static function arrayFromObject($item, $recurse, $regex)
	{
		if (is_object($item))
		{
			$result = array();

			foreach (get_object_vars($item) as $k => $v)
			{
				if (!$regex || preg_match($regex, $k))
				{
					if ($recurse)
					{
						$result[$k] = self::arrayFromObject($v, $recurse, $regex);
					}
					else
					{
						$result[$k] = $v;
					}
				}
			}
		}
		elseif (is_array($item))
		{
			$result = array();

			foreach ($item as $k => $v)
			{
				$result[$k] = self::arrayFromObject($v, $recurse, $regex);
			}
		}
		else
		{
			$result = $item;
		}

		return $result;
	}

	/**
	 * Extracts a column from an array of arrays or objects
	 *
	 * @param   array   $array  The source array
	 * @param   string  $index  The index of the column or name of object property
	 *
	 * @return  array  Column of values from the source array
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public static function getColumn(array $array, $index)
	{
		$result = array();

		foreach ($array as $item)
		{
			if (is_array($item) && isset($item[$index]))
			{
				$result[] = $item[$index];
			}
			elseif (is_object($item) && isset($item->$index))
			{
				$result[] = $item->$index;
			}
		}

		return $result;
	}

	/**
	 * Utility function to return a value from a named array or a specified default
	 *
	 * @param   array   $array    A named array
	 * @param   string  $name     The key to search for
	 * @param   mixed   $default  The default value to give if no key found
	 * @param   string  $type     Return type for the variable (INT, FLOAT, STRING, WORD, BOOLEAN, ARRAY)
	 *
	 * @return  mixed  The value from the source array
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public static function getValue(array $array, $name, $default = null, $type = '')
	{
		$result = null;

		if (isset($array[$name]))
		{
			$result = $array[$name];
		}

		// Handle the default case
		if (is_null($result))
		{
			$result = $default;
		}

		// Handle the type constraint
		switch (strtoupper($type))
		{
			case 'INT':
			case 'INTEGER':
				// Only use the first integer value
				@preg_match('/-?[0-9]+/', $result, $matches);
				$result = @(int) $matches[0];
				break;

			case 'FLOAT':
			case 'DOUBLE':
				// Only use the first floating point value
				@preg_match('/-?[0-9]+(\.[0-9]+)?/', $result, $matches);
				$result = @(float) $matches[0];
				break;

			case 'BOOL':
			case 'BOOLEAN':
				$result = (bool) $result;
				break;

			case 'ARRAY':
				if (!is_array($result))
				{
					$result = array($result);
				}
				break;

			case 'STRING':
				$result = (string) $result;
				break;

			case 'WORD':
				$result = (string) preg_replace('#\W#', '', $result);
				break;

			case 'NONE':
			default:
				// No casting necessary
				break;
		}

		return $result;
	}

	/**
	 * Takes an associative array of arrays and inverts the array keys to values using the array values as keys.
	 *
	 * Example:
	 * $input = array(
	 *     'New' => array('1000', '1500', '1750'),
	 *     'Used' => array('3000', '4000', '5000', '6000')
	 * );
	 * $output = ArrayHelper::invert($input);
	 *
	 * Output would be equal to:
	 * $output = array(
	 *     '1000' => 'New',
	 *     '1500' => 'New',
	 *     '1750' => 'New',
	 *     '3000' => 'Used',
	 *     '4000' => 'Used',
	 *     '5000' => 'Used',
	 *     '6000' => 'Used'
	 * );
	 *
	 * @param   array  $array  The source array.
	 *
	 * @return  array  The inverted array.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public static function invert(array $array)
	{
		$return = array();

		foreach ($array as $base => $values)
		{
			if (!is_array($values))
			{
				continue;
			}

			foreach ($values as $key)
			{
				// If the key isn't scalar then ignore it.
				if (is_scalar($key))
				{
					$return[$key] = $base;
				}
			}
		}

		return $return;
	}

	/**
	 * Method to determine if an array is an associative array.
	 *
	 * @param   array  $array  An array to test.
	 *
	 * @return  boolean  True if the array is an associative array.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public static function isAssociative($array)
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
	 * Pivots an array to create a reverse lookup of an array of scalars, arrays or objects.
	 *
	 * @param   array   $source  The source array.
	 * @param   string  $key     Where the elements of the source array are objects or arrays, the key to pivot on.
	 *
	 * @return  array  An array of arrays pivoted either on the value of the keys, or an individual key of an object or array.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public static function pivot(array $source, $key = null)
	{
		$result  = array();
		$counter = array();

		foreach ($source as $index => $value)
		{
			// Determine the name of the pivot key, and its value.
			if (is_array($value))
			{
				// If the key does not exist, ignore it.
				if (!isset($value[$key]))
				{
					continue;
				}

				$resultKey   = $value[$key];
				$resultValue = $source[$index];
			}
			elseif (is_object($value))
			{
				// If the key does not exist, ignore it.
				if (!isset($value->$key))
				{
					continue;
				}

				$resultKey   = $value->$key;
				$resultValue = $source[$index];
			}
			else
			{
				// Just a scalar value.
				$resultKey   = $value;
				$resultValue = $index;
			}

			// The counter tracks how many times a key has been used.
			if (empty($counter[$resultKey]))
			{
				// The first time around we just assign the value to the key.
				$result[$resultKey] = $resultValue;
				$counter[$resultKey] = 1;
			}
			elseif ($counter[$resultKey] == 1)
			{
				// If there is a second time, we convert the value into an array.
				$result[$resultKey] = array(
					$result[$resultKey],
					$resultValue,
				);
				$counter[$resultKey]++;
			}
			else
			{
				// After the second time, no need to track any more. Just append to the existing array.
				$result[$resultKey][] = $resultValue;
			}
		}

		unset($counter);

		return $result;
	}

	/**
	 * Utility function to sort an array of objects on a given field
	 *
	 * @param   array  $a              An array of objects
	 * @param   mixed  $k              The key (string) or a array of key to sort on
	 * @param   mixed  $direction      Direction (integer) or an array of direction to sort in [1 = Ascending] [-1 = Descending]
	 * @param   mixed  $caseSensitive  Boolean or array of booleans to let sort occur case sensitive or insensitive
	 * @param   mixed  $locale         Boolean or array of booleans to let sort occur using the locale language or not
	 *
	 * @return  array  The sorted array of objects
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public static function sortObjects(array $a, $k, $direction = 1, $caseSensitive = true, $locale = false)
	{
		if (!is_array($locale) || !is_array($locale[0]))
		{
			$locale = array($locale);
		}

		$sortCase      = (array) $caseSensitive;
		$sortDirection = (array) $direction;
		$key           = (array) $k;
		$sortLocale    = $locale;

		usort(
			$a, function($a, $b) use($sortCase, $sortDirection, $key, $sortLocale)
			{
				for ($i = 0, $count = count($key); $i < $count; $i++)
				{
					if (isset($sortDirection[$i]))
					{
						$direction = $sortDirection[$i];
					}

					if (isset($sortCase[$i]))
					{
						$caseSensitive = $sortCase[$i];
					}

					if (isset($sortLocale[$i]))
					{
						$locale = $sortLocale[$i];
					}

					$va = $a->$key[$i];
					$vb = $b->$key[$i];

					if ((is_bool($va) || is_numeric($va)) && (is_bool($vb) || is_numeric($vb)))
					{
						$cmp = $va - $vb;
					}
					elseif ($caseSensitive)
					{
						$cmp = Utf8String::strcmp($va, $vb, $locale);
					}
					else
					{
						$cmp = Utf8String::strcasecmp($va, $vb, $locale);
					}

					if ($cmp > 0)
					{
						return $direction;
					}

					if ($cmp < 0)
					{
						return -$direction;
					}
				}

				return 0;
			}
		);

		return $a;
	}

	/**
	 * Multidimensional array safe unique test
	 *
	 * @param   array  $array  The array to make unique.
	 *
	 * @return  array
	 *
	 * @see     http://php.net/manual/en/function.array-unique.php
	 * @since   {DEPLOY_VERSION}
	 */
	public static function arrayUnique(array $array)
	{
		$array = array_map('serialize', $array);
		$array = array_unique($array);
		$array = array_map('unserialize', $array);

		return $array;
	}

	/**
	 * An improved array_search that allows for partial matching
	 * of strings values in associative arrays.
	 *
	 * @param   string   $needle         The text to search for within the array.
	 * @param   array    $haystack       Associative array to search in to find $needle.
	 * @param   boolean  $caseSensitive  True to search case sensitive, false otherwise.
	 *
	 * @return  mixed    Returns the matching array $key if found, otherwise false.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public static function arraySearch($needle, array $haystack, $caseSensitive = true)
	{
		foreach ($haystack as $key => $value)
		{
			$searchFunc = ($caseSensitive) ? 'strpos' : 'stripos';

			if ($searchFunc($value, $needle) === 0)
			{
				return $key;
			}
		}

		return false;
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
	 *
	 * @since   {DEPLOY_VERSION}
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

	/**
	 * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
	 * keys to arrays rather than overwriting the value in the first array with the duplicate
	 * value in the second array, as array_merge does. I.e., with array_merge_recursive,
	 * this happens (documented behavior):
	 *
	 * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
	 *     => array('key' => array('org value', 'new value'));
	 *
	 * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
	 * Matching keys' values in the second array overwrite those in the first array, as is the
	 * case with array_merge, i.e.:
	 *
	 * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
	 *     => array('key' => array('new value'));
	 *
	 * Parameters are passed by reference, though only for performance reasons. They're not
	 * altered by this function.
	 *
	 * @param   array    &$array1   Array to be merge.
	 * @param   array    &$array2   Array to be merge.
	 * @param   boolean  $recursive Recursive merge, default is true.
	 *
	 * @return  array Merged array.
	 *
	 * @since   {DEPLOY_VERSION}
	 *
	 * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
	 * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
	 */
	public static function merge(array &$array1, array &$array2, $recursive = true)
	{
		$merged = $array1;

		foreach ($array2 as $key => &$value)
		{
			if ($recursive && is_array($value) && isset($merged[$key]) && is_array($merged[$key]))
			{
				$merged[$key] = static::merge($merged [$key], $value);
			}
			else
			{
				$merged[$key] = $value;
			}
		}

		return $merged;
	}

	/**
	 * setByPath
	 *
	 * @param mixed  &$data
	 * @param string $paths
	 * @param mixed  $value
	 * @param string $separator
	 * @param string $type
	 *
	 * @return  boolean
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public static function setByPath(&$data, $paths, $value, $separator = '.', $type = 'array')
	{
		if (empty($paths))
		{
			return false;
		}

		$args = is_array($paths) ? $paths : explode($separator, $paths);

		/**
		 * A closure as inner function to create data store.
		 *
		 * @param $type
		 *
		 * @return  array
		 *
		 * @throws \InvalidArgumentException
		 */
		$createStore = function($type)
		{
			if (strtolower($type) == 'array')
			{
				return array();
			}

			if (class_exists($type))
			{
				return new $type;
			}

			throw new \InvalidArgumentException(sprintf('Type %s not supported of class not exiists', $type));
		};

		$dataTmp = $data;

		foreach ($args as $arg)
		{
			if (is_object($dataTmp))
			{
				if (empty($dataTmp->$arg))
				{
					$dataTmp->$arg = $createStore($type);
				}

				$dataTmp = &$dataTmp->$arg;
			}
			elseif (is_array($dataTmp))
			{
				if (empty($dataTmp[$arg]))
				{
					$dataTmp[$arg] = $createStore($type);
				}

				$dataTmp = &$dataTmp[$arg];
			}
			else
			{
				$dataTmp = &$createStore($type);
			}
		}

		$dataTmp = $value;

		return true;
	}

	/**
	 * Recursive dump variables and limit by level.
	 *
	 * @param   mixed  $data   The variable you want to dump.
	 * @param   int    $level  The level number to limit recursive loop.
	 *
	 * @return  string  Dumped data.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public static function dump($data, $level = 5)
	{
		static $innerLevel = 1;

		static $tabLevel = 1;

		$self = __FUNCTION__;

		$type       = gettype($data);
		$tabs       = str_repeat('    ', $tabLevel);
		$quoteTabes = str_repeat('    ', $tabLevel - 1);
		$output     = '';
		$elements   = array();

		$recursiveType = array('object', 'array');

		// Recursive
		if (in_array($type, $recursiveType))
		{
			// If type is object, try to get properties by Reflection.
			if ($type == 'object')
			{
				$output     = get_class($data) . ' ' . ucfirst($type);
				$ref        = new \ReflectionObject($data);
				$properties = $ref->getProperties();

				foreach ($properties as $property)
				{
					$property->setAccessible(true);

					$pType = $property->getName();

					if ($property->isProtected())
					{
						$pType .= ":protected";
					}
					elseif ($property->isPrivate())
					{
						$pType .= ":" . $property->class . ":private";
					}

					if ($property->isStatic())
					{
						$pType .= ":static";
					}

					$elements[$pType] = $property->getValue($data);
				}
			}
			// If type is array, just retun it's value.
			elseif ($type == 'array')
			{
				$output   = ucfirst($type);
				$elements = $data;
			}

			// Start dumping data
			if ($level == 0 || $innerLevel < $level)
			{
				// Start recursive print
				$output .= "\n{$quoteTabes}(";

				foreach ($elements as $key => $element)
				{
					$output .= "\n{$tabs}[{$key}] => ";

					// Increment level
					$tabLevel = $tabLevel + 2;
					$innerLevel++;

					$output  .= in_array(gettype($element), $recursiveType) ? static::$self($element, $level) : $element;

					// Decrement level
					$tabLevel = $tabLevel - 2;
					$innerLevel--;
				}

				$output .= "\n{$quoteTabes})\n";
			}
			else
			{
				$output .= "\n{$quoteTabes}*MAX LEVEL*\n";
			}
		}
		else
		{
			$output = $data;
		}

		return $output;
	}
}
