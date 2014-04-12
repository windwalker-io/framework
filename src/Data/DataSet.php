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
	 * Bind data array into self.
	 *
	 * @param array $dataset An aray of multiple data.
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

		return $this->$offset;
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
		$this->data[$offset] = $value;
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
	 * @return  array The data to be iterator.
	 */
	public function getIterator()
	{
		return $this->data;
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
		return json_encode($this->data);
	}
}
