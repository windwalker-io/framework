<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Registry;

use Windwalker\Registry\Helper\RegistryHelper;

if (!interface_exists('JsonSerializable'))
{
	include_once __DIR__ . '/Compat/JsonSerializable.php';
}

/**
 * Registry class
 *
 * @since  {DEPLOY_VERSION}
 */
class Registry implements \JsonSerializable, \ArrayAccess
{
	/**
	 * Registry Object
	 *
	 * @var    object
	 * @since  {DEPLOY_VERSION}
	 */
	protected $data;

	/**
	 * Constructor
	 *
	 * @param   mixed   $data   The data to bind to the new Registry object.
	 * @param   string  $format The format of input, only work when first argument is string.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function __construct($data = null, $format = 'json')
	{
		// Instantiate the internal data object.
		$this->data = new \stdClass;

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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
	 */
	public function __toString()
	{
		try
		{
			return $this->toString();
		}
		catch (\Exception $e)
		{
			return (string) $e;
		}
	}

	/**
	 * Implementation for the JsonSerializable interface.
	 * Allows us to pass Registry objects to json_encode.
	 *
	 * @return  object
	 *
	 * @since   {DEPLOY_VERSION}
	 * @note    The interface is only present in PHP 5.4 and up.
	 */
	public function jsonSerialize()
	{
		return $this->data;
	}

	/**
	 * Sets a default value if not already assigned.
	 *
	 * @param   string  $key      The name of the parameter.
	 * @param   mixed   $default  An optional value for the parameter.
	 *
	 * @return  mixed  The value set, or the default if the value was not previously set (or null).
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function def($key, $default = '')
	{
		$value = $this->get($key, $default);
		$this->set($key, $value);

		return $value;
	}

	/**
	 * Check if a registry path exists.
	 *
	 * @param   string  $path  Registry path (e.g. foo.content.showauthor)
	 *
	 * @return  boolean
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function exists($path)
	{
		// Explode the registry path into an array
		$nodes = explode('.', $path);

		if ($nodes)
		{
			// Initialize the current node to be the registry root.
			$node = $this->data;

			// Traverse the registry to find the correct node for the result.
			for ($i = 0, $n = count($nodes); $i < $n; $i++)
			{
				if (isset($node->$nodes[$i]))
				{
					$node = $node->$nodes[$i];
				}
				else
				{
					break;
				}

				if ($i + 1 == $n)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get a registry value.
	 *
	 * @param   string  $path     Registry path (e.g. foo.content.showauthor)
	 * @param   mixed   $default  Optional default value, returned if the internal value is null.
	 *
	 * @return  mixed  Value of entry or null
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function get($path, $default = null)
	{
		$result = $default;

		if (!strpos($path, '.'))
		{
			return (isset($this->data->$path) && $this->data->$path !== null && $this->data->$path !== '') ? $this->data->$path : $default;
		}

		// Explode the registry path into an array
		$nodes = explode('.', $path);

		// Initialize the current node to be the registry root.
		$node = $this->data;
		$found = false;

		// Traverse the registry to find the correct node for the result.
		foreach ($nodes as $n)
		{
			if (isset($node->$n))
			{
				$node = $node->$n;
				$found = true;
			}
			else
			{
				$found = false;
				break;
			}
		}

		if ($found && $node !== null && $node !== '')
		{
			$result = $node;
		}

		return $result;
	}

	/**
	 * Clear all data.
	 *
	 * @return  static
	 */
	public function clear()
	{
		$this->data = new \stdClass;

		return $this;
	}

	/**
	 * Load a associative array of values into the default namespace
	 *
	 * @param   array  $array  Associative array of value to load
	 *
	 * @return  Registry  Return this object to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
	 */
	public function loadString($data, $format = 'JSON', $options = array())
	{
		// Load a string into the given namespace [or default namespace if not given]
		$class = $this->getFormatClass($format);

		$obj = $class::stringToObject($data, $options);
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
	 * @since   {DEPLOY_VERSION}
	 */
	public function merge(Registry $source, $recursive = true)
	{
		$this->bindData($this->data, $source->toArray(), $recursive, false);

		return $this;
	}

	/**
	 * Checks whether an offset exists in the iterator.
	 *
	 * @param   mixed  $offset  The array offset.
	 *
	 * @return  boolean  True if the offset exists, false otherwise.
	 *
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
	 */
	public function offsetUnset($offset)
	{
		$this->set($offset, null);
	}

	/**
	 * Set a registry value.
	 *
	 * @param   string  $path   Registry Path (e.g. foo.content.showauthor)
	 * @param   mixed   $value  Value of entry
	 *
	 * @return  mixed  The value of the that has been set.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function set($path, $value)
	{
		$result = null;

		/**
		 * Explode the registry path into an array and remove empty
		 * nodes that occur as a result of a double dot. ex: windwalker..test
		 * Finally, re-key the array so they are sequential.
		 */
		$nodes = array_values(array_filter(explode('.', $path), 'strlen'));

		if ($nodes)
		{
			// Initialize the current node to be the registry root.
			$node = $this->data;

			// Traverse the registry to find the correct node for the result.
			for ($i = 0, $n = count($nodes) - 1; $i < $n; $i++)
			{
				if (!isset($node->$nodes[$i]) && ($i != $n))
				{
					$node->$nodes[$i] = new \stdClass;
				}

				$node = $node->$nodes[$i];
			}

			// Get the old value if exists so we can return it
			$result = $node->$nodes[$i] = $value;
		}

		return $result;
	}

	/**
	 * Transforms a namespace to an array
	 *
	 * @return  array  An associative array holding the namespace data
	 *
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
	 */
	public function toObject($class = '\stdClass')
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
	 * @since   {DEPLOY_VERSION}
	 */
	public function toString($format = 'JSON', $options = array())
	{
		$class = $this->getFormatClass($format);

		return $class::objectToString($this->data, $options);
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
	 * @param   object  $parent    The parent object on which to attach the data values.
	 * @param   mixed   $data      An array or object of data to bind to the parent object.
	 * @param   boolean $recursive True to support recursive bindData.
	 * @param   boolean $allowNull Allow null
	 *
	 * @return  void
	 */
	protected function bindData($parent, $data, $recursive = true, $allowNull = true)
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

		foreach ($data as $k => $v)
		{
			if (!$allowNull && !(($v !== null) && ($v !== '')))
			{
				continue;
			}

			if ($recursive && ((is_array($v) && RegistryHelper::isAssociativeArray($v)) || is_object($v)))
			{
				if (!isset($parent->$k))
				{
					$parent->$k = new \stdClass;
				}

				$this->bindData($parent->$k, $v);
			}
			else
			{
				$parent->$k = $v;
			}
		}
	}

	/**
	 * Method to recursively convert an object of data to an array.
	 *
	 * @param   object  $data  An object of data to return as an array.
	 *
	 * @return  array  Array representation of the input object.
	 *
	 * @since   {DEPLOY_VERSION}
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
		$array = array();

		$this->toFlatten($separator, $this->data, $array);

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
	protected function toFlatten($separator = '.', $data = null, &$array = array(), $prefix = '')
	{
		$data = (array) $data;

		foreach ($data as $k => $v)
		{
			$key = $prefix ? $prefix . $separator . $k : $k;

			if (is_object($v) || is_array($v))
			{
				$this->toFlatten($separator, $v, $array, $key);
			}
			else
			{
				$array[$key] = $v;
			}
		}
	}
}
