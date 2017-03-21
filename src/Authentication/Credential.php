<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Authentication;

/**
 * The Credential class.
 * 
 * @since  2.0
 */
class Credential implements \ArrayAccess
{
	/**
	 * Class init.
	 *
	 * @param array|object $data
	 */
	public function __construct($data = [])
	{
		$this->bind($data);
	}

	/**
	 * __get
	 *
	 * @param string $name
	 *
	 * @return  mixed
	 */
	public function __get($name)
	{
		if (isset($this->$name))
		{
			return $this->$name;
		}

		return null;
	}

	/**
	 * bind
	 *
	 * @param array|object $values
	 *
	 * @return  static
	 */
	public function bind($values = [])
	{
		if (is_object($values))
		{
			$values = get_object_vars($values);
		}

		if (!is_array($values))
		{
			throw new \InvalidArgumentException('Please give me array or object');
		}

		foreach ($values as $key => $value)
		{
			$this->$key = $value;
		}

		return $this;
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
		return property_exists($this, $offset);
	}

	/**
	 * Get a property.
	 *
	 * @param mixed $offset Offset key.
	 *
	 * @throws  \InvalidArgumentException
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
	 * @throws  \InvalidArgumentException
	 * @return  void
	 */
	public function offsetSet($offset, $value)
	{
		$this->$offset = $value;
	}

	/**
	 * Unset a property.
	 *
	 * @param mixed $offset Offset key to unset.
	 *
	 * @throws  \InvalidArgumentException
	 * @return  void
	 */
	public function offsetUnset($offset)
	{
		if (property_exists($this, $offset))
		{
			unset($this->$offset);
		}
	}
}
