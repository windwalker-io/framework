<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Data;

use Traversable;

/**
 * Class Data
 */
class Data implements \IteratorAggregate
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
	 * @return Traversable An instance of an object implementing Iterator or Traversable
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this);
	}
}
