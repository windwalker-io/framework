<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Registry;

if (!interface_exists('JsonSerializable'))
{
	include_once __DIR__ . '/Compat/JsonSerializable.php';
}

/**
 * Registry class
 *
 * @since  2.0
 */
class Registry implements \JsonSerializable, \ArrayAccess, \IteratorAggregate, \Countable
{
	/**
	 * Property separator.
	 *
	 * @var  string
	 */
	protected $separator = '.';

	/**
	 * Registry data store.
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $data = array();

	/**
	 * Constructor
	 *
	 * @param   mixed   $data   The data to bind to the new Registry object.
	 * @param   string  $format The format of input, only work when first argument is string.
	 *
	 * @since   2.0
	 */
	public function __construct($data = null, $format = 'json')
	{
		// Optionally load supplied data.
		if (is_array($data) || is_object($data))
		{
			$this->bindData($this->data, $data);
		}
		elseif (!empty($data) && is_string($data))
		{
			$this->loadString($data, $format);
		}
	}

	/**
	 * Magic function to clone the registry object.
	 *
	 * @return  Registry
	 *
	 * @since   2.0
	 */
	public function __clone()
	{
		$this->data = unserialize(serialize($this->data));
	}

	/**
	 * Magic function to render this object as a string using default args of toString method.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function __toString()
	{
		try
		{
			return $this->toString();
		}
		catch (\Exception $e)
		{
			return trigger_error((string) $e, E_USER_ERROR);
		}
	}

	/**
	 * Implementation for the JsonSerializable interface.
	 * Allows us to pass Registry objects to json_encode.
	 *
	 * @return  array
	 *
	 * @since   2.0
	 * @note    The interface is only present in PHP 5.4 and up.
	 */
	public function jsonSerialize()
	{
		return $this->data;
	}

	/**
	 * Sets a default value if not already assigned.
	 *
	 * @param   string  $path   The name of the parameter.
	 * @param   mixed   $value  An optional value for the parameter.
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since   2.0
	 */
	public function def($path, $value = '')
	{
		$value = $this->get($path, $value);
		$this->set($path, $value);

		return $this;
	}

	/**
	 * Check if a registry path exists.
	 *
	 * @param   string  $path  Registry path (e.g. foo.content.showauthor)
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function exists($path)
	{
		return !is_null($this->get($path));
	}

	/**
	 * Get a registry value.
	 *
	 * @param   string  $path       Registry path (e.g. foo.content.showauthor)
	 * @param   mixed   $default    Optional default value, returned if the internal value is null.
	 *
	 * @return  mixed  Value of entry or null
	 *
	 * @since   2.0
	 */
	public function get($path, $default = null)
	{
		$result = RegistryHelper::getByPath($this->data, $path, $this->separator);

		return !is_null($result) ? $result : $default;
	}

	/**
	 * Clear all data.
	 *
	 * @return  static
	 */
	public function clear()
	{
		$this->data = array();

		return $this;
	}

	/**
	 * Load a associative array of values into the default namespace
	 *
	 * @param   array  $array  Associative array of value to load
	 *
	 * @return  Registry  Return this object to support chaining.
	 *
	 * @since   2.0
	 */
	public function loadArray($array)
	{
		$this->bindData($this->data, $array);

		return $this;
	}

	/**
	 * Load the public variables of the object into the default namespace.
	 *
	 * @param   object  $object  The object holding the publics to load
	 *
	 * @return  Registry  Return this object to support chaining.
	 *
	 * @since   2.0
	 */
	public function loadObject($object)
	{
		$this->bindData($this->data, $object);

		return $this;
	}

	/**
	 * Load the contents of a file into the registry
	 *
	 * @param   string  $file     Path to file to load
	 * @param   string  $format   Format of the file [optional: defaults to JSON]
	 * @param   array   $options  Options used by the formatter
	 *
	 * @return  Registry  Return this object to support chaining.
	 *
	 * @since   2.0
	 */
	public function loadFile($file, $format = 'JSON', $options = array())
	{
		if (strtolower($format) == 'php')
		{
			$data = include $file;

			return $this->loadArray($data, $format, $options);
		}

		$data = file_get_contents($file);

		return $this->loadString($data, $format, $options);
	}

	/**
	 * Load a string into the registry
	 *
	 * @param   string  $data     String to load into the registry
	 * @param   string  $format   Format of the string
	 * @param   array   $options  Options used by the formatter
	 *
	 * @return  Registry  Return this object to support chaining.
	 *
	 * @since   2.0
	 */
	public function loadString($data, $format = 'JSON', $options = array())
	{
		// Load a string into the given namespace [or default namespace if not given]
		$class = $this->getFormatClass($format);

		$obj = $class::stringToStruct($data, $options);
		$this->loadObject($obj);

		return $this;
	}

	/**
	 * Merge a Registry object into this one
	 *
	 * @param   Registry  $source     Source Registry object to merge.
	 * @param   boolean   $recursive  True to support recursive merge the children values.
	 *
	 * @return  Registry  Return this object to support chaining.
	 *
	 * @since   2.0
	 */
	public function merge(Registry $source, $recursive = true)
	{
		$this->bindData($this->data, $source->toArray(), $recursive, false);

		return $this;
	}

	/**
	 * extract
	 *
	 * @param string $path
	 *
	 * @return  static
	 */
	public function extract($path)
	{
		return new static($this->get($path));
	}

	/**
	 * getRaw
	 *
	 * @return  \stdClass
	 */
	public function getRaw()
	{
		return $this->data;
	}

	/**
	 * Checks whether an offset exists in the iterator.
	 *
	 * @param   mixed  $offset  The array offset.
	 *
	 * @return  boolean  True if the offset exists, false otherwise.
	 *
	 * @since   2.0
	 */
	public function offsetExists($offset)
	{
		return (boolean) ($this->get($offset) !== null);
	}

	/**
	 * Gets an offset in the iterator.
	 *
	 * @param   mixed  $offset  The array offset.
	 *
	 * @return  mixed  The array value if it exists, null otherwise.
	 *
	 * @since   2.0
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * Sets an offset in the iterator.
	 *
	 * @param   mixed  $offset  The array offset.
	 * @param   mixed  $value   The array value.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}

	/**
	 * Unsets an offset in the iterator.
	 *
	 * @param   mixed  $offset  The array offset.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function offsetUnset($offset)
	{
		$this->set($offset, null);
	}

	/**
	 * Set a registry value.
	 *
	 * @param   string  $path       Registry Path (e.g. foo.content.showauthor)
	 * @param   mixed   $value      Value of entry.
	 *
	 * @return   static  Return self to support chaining.
	 *
	 * @since   2.0
	 */
	public function set($path, $value)
	{
		RegistryHelper::setByPath($this->data, $path, $value, $this->separator);

		return $this;
	}

	/**
	 * Transforms a namespace to an array
	 *
	 * @return  array  An associative array holding the namespace data
	 *
	 * @since   2.0
	 */
	public function toArray()
	{
		return (array) $this->asArray($this->data);
	}

	/**
	 * Transforms a namespace to an object
	 *
	 * @param   string  $class  The class of object.
	 *
	 * @return  object   An an object holding the namespace data
	 *
	 * @since   2.0
	 */
	public function toObject($class = 'stdClass')
	{
		return RegistryHelper::toObject($this->data, $class);
	}

	/**
	 * Get a namespace in a given string format
	 *
	 * @param   string  $format   Format to return the string in
	 * @param   mixed   $options  Parameters used by the formatter, see formatters for more info
	 *
	 * @return  string   Namespace in string format
	 *
	 * @since   2.0
	 */
	public function toString($format = 'JSON', $options = array())
	{
		$class = $this->getFormatClass($format);

		return $class::structToString($this->data, $options);
	}

	/**
	 * getFormatClass
	 *
	 * @param string $format
	 *
	 * @throws  \DomainException
	 * @return  string|\Windwalker\Registry\Format\FormatInterface
	 */
	protected function getFormatClass($format)
	{
		// Return a namespace in a given format
		$class = __NAMESPACE__ . '\\Format\\' . ucfirst(strtolower($format)) . 'Format';

		if (!class_exists($class))
		{
			throw new \DomainException(sprintf('Registry format: %s not supported. Class: %s not found.', $format, $class));
		}

		return $class;
	}

	/**
	 * Method to recursively bind data to a parent object.
	 *
	 * @param   array   $parent    The parent object on which to attach the data values.
	 * @param   mixed   $data      An array or object of data to bind to the parent object.
	 * @param   boolean $recursive True to support recursive bindData.
	 * @param   boolean $allowNull Allow null
	 *
	 * @return  void
	 */
	protected function bindData(&$parent, $data, $recursive = true, $allowNull = true)
	{
		// Ensure the input data is an array.
		if (is_object($data))
		{
			$data = get_object_vars($data);
		}
		else
		{
			$data = (array) $data;
		}

		foreach ($data as $key => $value)
		{
			if (!$allowNull && !(($value !== null) && ($value !== '')))
			{
				continue;
			}

			if ($recursive && (is_array($value) || is_object($value)))
			{
				if (!isset($parent[$key]))
				{
					$parent[$key] = array();
				}

				$this->bindData($parent[$key], $value);
			}
			else
			{
				$parent[$key] = $value;
			}
		}
	}

	/**
	 * Method to recursively convert an object of data to an array.
	 *
	 * @param   mixed  $data  An object of data to return as an array.
	 *
	 * @return  array  Array representation of the input object.
	 *
	 * @since   2.0
	 */
	protected function asArray($data)
	{
		$array = array();

		if (is_object($data))
		{
			$data = get_object_vars($data);
		}

		foreach ($data as $k => $v)
		{
			if (is_object($v) || is_array($v))
			{
				$array[$k] = $this->asArray($v);
			}
			else
			{
				$array[$k] = $v;
			}
		}

		return $array;
	}

	/**
	 * Dump to on dimension array.
	 *
	 * @param string $separator The key separator.
	 *
	 * @return  string[] Dumped array.
	 */
	public function flatten($separator = '.')
	{
		return RegistryHelper::flatten($this->data, $separator);
	}

	/**
	 * Method to get property Separator
	 *
	 * @return  string
	 *
	 * @since   2.1
	 */
	public function getSeparator()
	{
		return $this->separator;
	}

	/**
	 * Method to set property separator
	 *
	 * @param   string $separator
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since   2.1
	 */
	public function setSeparator($separator)
	{
		$this->separator = $separator;

		return $this;
	}

	/**
	 * Append value to a path in registry
	 *
	 * @param   string  $path   Parent registry Path (e.g. joomla.content.showauthor)
	 * @param   mixed   $value  Value of entry
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since   2.1
	 */
	public function append($path, $value)
	{
		$node = $this->get($path);

		if (!$node)
		{
			$node = array();
		}
		elseif (is_object($node))
		{
			$node = get_object_vars($node);
		}

		array_push($node, $value);

		$this->set($path, $node);

		return $this;
	}

	/**
	 * Gets this object represented as an ArrayIterator.
	 *
	 * This allows the data properties to be accessed via a foreach statement.
	 *
	 * @return  \ArrayIterator  This object represented as an ArrayIterator.
	 *
	 * @see     IteratorAggregate::getIterator()
	 * @since   2.1
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->data);
	}

	/**
	 * Count elements of the data object
	 *
	 * @return  integer  The custom count as an integer.
	 *
	 * @link    http://php.net/manual/en/countable.count.php
	 * @since   2.1
	 */
	public function count()
	{
		return count($this->data);
	}
}
