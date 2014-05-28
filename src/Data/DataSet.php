<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Data;

/**
 * The Data set to store multiple data.
 *
 * @since 1.0
 */
class DataSet implements DatasetInterface, \IteratorAggregate, \ArrayAccess, \Serializable, \Countable, \JsonSerializable
{
	/**
	 * The data store.
	 *
	 * @var  array
	 */
	protected $data = array();

	/**
	 * Constructor.
	 *
	 * @param mixed $data
	 */
	public function __construct($data = null)
	{
		if ($data)
		{
			$this->bind($data);
		}
	}

	/**
	 * Bind data array into self.
	 *
	 * @param array $dataset An array of multiple data.
	 *
	 * @throws \InvalidArgumentException
	 * @return  DataSet Return self to support chaining.
	 */
	public function bind($dataset)
	{
		if ($dataset instanceof \Traversable)
		{
			$dataset = iterator_to_array($dataset);
		}
		elseif (is_object($dataset))
		{
			$dataset = array($dataset);
		}
		elseif (!is_array($dataset))
		{
			throw new \InvalidArgumentException('Need an array or object');
		}

		foreach ($dataset as $data)
		{
			if (!($data instanceof Data))
			{
				$data = new Data($data);
			}

			$this[] = $data;
		}

		return $this;
	}

	/**
	 * The magic get method is used to get a list of properties from the objects in the data set.
	 *
	 * Example: $array = $dataSet->foo;
	 *
	 * This will return a column of the values of the 'foo' property in all the objects
	 * (or values determined by custom property setters in the individual Data's).
	 * The result array will contain an entry for each object in the list (compared to __call which may not).
	 * The keys of the objects and the result array are maintained.
	 *
	 * @param   string  $property  The name of the data property.
	 *
	 * @return  array  An associative array of the values.
	 */
	public function __get($property)
	{
		$return = array();

		// Iterate through the objects.
		foreach ($this->data as $key => $data)
		{
			// Get the property.
			$return[$key] = $data->$property;
		}

		return $return;
	}

	/**
	 * The magic isset method is used to check the state of an object property using the iterator.
	 *
	 * Example: $array = isset($objectList->foo);
	 *
	 * @param   string  $property  The name of the property.
	 *
	 * @return  boolean  True if the property is set in any of the objects in the data set.
	 */
	public function __isset($property)
	{
		$return = array();

		// Iterate through the objects.
		foreach ($this->data as $data)
		{
			// Check the property.
			$return[] = isset($data->$property);
		}

		return in_array(true, $return, true) ? true : false;
	}

	/**
	 * The magic set method is used to set an object property using the iterator.
	 *
	 * Example: $objectList->foo = 'bar';
	 *
	 * This will set the 'foo' property to 'bar' in all of the objects
	 * (or a value determined by custom property setters in the Data).
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value to give the data property.
	 *
	 * @return  void
	 */
	public function __set($property, $value)
	{
		// Iterate through the objects.
		foreach ($this->data as $data)
		{
			// Set the property.
			$data->$property = $value;
		}
	}

	/**
	 * The magic unset method is used to unset an object property using the iterator.
	 *
	 * Example: unset($objectList->foo);
	 *
	 * This will unset all of the 'foo' properties in the list of Data\Object's.
	 *
	 * @param   string  $property  The name of the property.
	 *
	 * @return  void
	 */
	public function __unset($property)
	{
		// Iterate through the objects.
		foreach ($this->data as $data)
		{
			unset($data->$property);
		}
	}

	/**
	 * Property is exist or not.
	 *
	 * @param mixed $offset Property key.
	 *
	 * @return  boolean
	 */
	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	/**
	 * Get a value of property.
	 *
	 * @param mixed $offset Property key.
	 *
	 * @return  mixed The value of this property.
	 */
	public function offsetGet($offset)
	{
		if (empty($this->data[$offset]))
		{
			return null;
		}

		return $this->data[$offset];
	}

	/**
	 * Clears the objects in the data set.
	 *
	 * @return  DataSet  Returns itself to allow chaining.
	 */
	public function clear()
	{
		$this->data = array();

		return $this;
	}

	/**
	 * Set value to property
	 *
	 * @param mixed $offset Property key.
	 * @param mixed $value  Property value to set.
	 *
	 * @return  void
	 */
	public function offsetSet($offset, $value)
	{
		if ($offset)
		{
			$this->data[$offset] = $value;
		}
		else
		{
			array_push($this->data, $value);
		}
	}

	/**
	 * Unset a property.
	 *
	 * @param mixed $offset Key to unset.
	 *
	 * @return  void
	 */
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}

	/**
	 * Get the data store for iterate.
	 *
	 * @return  \Traversable The data to be iterator.
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->data);
	}

	/**
	 * Serialize data.
	 *
	 * @return  string Serialized data string.
	 */
	public function serialize()
	{
		return serialize($this->data);
	}

	/**
	 * Unserialize the data.
	 *
	 * @param string $serialized THe serialized data string.
	 *
	 * @return  DataSet Support chaining.
	 */
	public function unserialize($serialized)
	{
		$this->data = unserialize($serialized);

		return $this;
	}

	/**
	 * Count data.
	 *
	 * @return  int
	 */
	public function count()
	{
		return count($this->data);
	}

	/**
	 * Serialize to json format.
	 *
	 * @return  string Encoded json string.
	 */
	public function jsonSerialize()
	{
		return $this->data;
	}

	/**
	 * Is this data set empty?
	 *
	 * @return  boolean Tru if empty.
	 */
	public function isNull()
	{
		return empty($this->data);
	}
}
