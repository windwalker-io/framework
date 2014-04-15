<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Data;

/**
 * Data object to store values.
 */
class Data implements DataInterface, \IteratorAggregate, \ArrayAccess, \Countable
{
	/**
	 * Constrictor.
	 *
	 * @param mixed $data
	 */
	public function __construct($data = null)
	{
		if (null !== $data)
		{
			$this->bind($data);
		}
	}

	/**
	 * bind
	 *
	 * @param      $values
	 * @param bool $replaceNulls
	 *
	 * @return  $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function bind($values, $replaceNulls = false)
	{
		// Check properties type.
		if (!is_array($values) && !is_object($values))
		{
			throw new \InvalidArgumentException(sprintf('Please bind array or object, %s given.', gettype($values)));
		}

		// If is Traversable, get iterator.
		if ($values instanceof \Traversable)
		{
			$values = iterator_to_array($values);
		}
		// If is object, convert it to array
		elseif (is_object($values))
		{
			$values = (array) $values;
		}

		// Bind the properties.
		foreach ($values as $field => $value)
		{
			// Check if the value is null and should be bound.
			if ($value === null && !$replaceNulls)
			{
				continue;
			}

			// Set the property.
			$this->$field = $value;
		}

		return $this;
	}

	/**
	 * set
	 *
	 * @param string $field
	 * @param mixed  $value
	 *
	 * @return  Data
	 */
	public function set($field, $value = null)
	{
		$this->$field = $value;

		return $this;
	}

	/**
	 * get
	 *
	 * @param string $field
	 * @param mixed  $default
	 *
	 * @return  mixed
	 */
	public function get($field, $default = null)
	{
		if (isset($this->$field))
		{
			return $this->$field;
		}

		return $default;
	}

	/**
	 * set
	 *
	 * @param string $field
	 * @param mixed  $value
	 *
	 * @return  Data
	 */
	public function __set($field, $value = null)
	{
		return $this->set($field, $value);
	}

	/**
	 * get
	 *
	 * @param string $field
	 *
	 * @return  mixed
	 */
	public function __get($field)
	{
		return $this->get($field);
	}

	/**
	 * Retrieve an external iterator
	 *
	 * @return \Traversable An instance of an object implementing Iterator or Traversable
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this);
	}

	/**
	 * Is a property exists or not.
	 *
	 * @param mixed $offset Offset key.
	 *
	 * @return  boolean
	 */
	public function offsetExists($offset)
	{
		return isset($this->$offset);
	}

	/**
	 * Get a property.
	 *
	 * @param mixed $offset Offset key.
	 *
	 * @return  mixed The value to return.
	 */
	public function offsetGet($offset)
	{
		return $this->$offset;
	}

	/**
	 * Set a value to property.
	 *
	 * @param mixed $offset Offset key.
	 * @param mixed $value  The value to set.
	 *
	 * @return  void
	 */
	public function offsetSet($offset, $value)
	{
		$this->$offset = $value;
	}

	/**
	 * Unset a propeerty.
	 *
	 * @param mixed $offset Offset key to unset.
	 *
	 * @return  void
	 */
	public function offsetUnset($offset)
	{
		unset($this->$offset);
	}

	/**
	 * Count this object.
	 *
	 * @return  int
	 */
	public function count()
	{
		return count(get_object_vars($this));
	}

	/**
	 * isNull
	 *
	 * @return  boolean
	 */
	public function isNull()
	{
		return (boolean) !count($this);
	}
}
