<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Option;

/**
 * Option set to store options and resolve aliases.
 *
 * @since 2.0
 */
class OptionSet implements \IteratorAggregate, \ArrayAccess, \Countable, \Serializable
{
	/**
	 * Property options.
	 *
	 * @var  Option[]
	 */
	protected $options = array();

	/**
	 * Add a new option.
	 *
	 * @param   Option  $option  Option object.
	 *
	 * @return  OptionSet Return self to support chaining.
	 *
	 * @since   2.0
	 */
	public function addOption(Option $option)
	{
		$this->offsetSet($option->getName(), $option);

		return $this;
	}

	/**
	 * Retrieve an external iterator
	 *
	 * @return \Traversable An instance of an object implementing Iterator or Traversable
	 *
	 * @since   2.0
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->options);
	}

	/**
	 * Find option by name or alias.
	 *
	 * @param   string  $name  The name or alias.
	 *
	 * @return  Option  The found option object.
	 *
	 * @since   2.0
	 */
	protected function findOption($name)
	{
		foreach ($this->options as $option)
		{
			if ($option->hasAlias($name))
			{
				return $option;
			}
		}

		return null;
	}

	/**
	 * Is a option exists?
	 *
	 * @param   mixed  $name  Option name.
	 *
	 * @return  boolean True if option exists.
	 *
	 * @since   2.0
	 */
	public function offsetExists($name)
	{
		return (bool) $this->findOption($name);
	}

	/**
	 * Get an option by name.
	 *
	 * @param   mixed   $name  Option name to get option.
	 *
	 * @return  Option|null  Return option object if exists.
	 *
	 * @since   2.0
	 */
	public function offsetGet($name)
	{
		return $this->findOption($name);
	}

	/**
	 * Set a new option.
	 *
	 * @param   string  $name    No use here, we use option name.
	 * @param   Option  $option  The option object to set in this set.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function offsetSet($name, $option)
	{
		$name = $option->getName();

		$this->options[$name] = $option;
	}

	/**
	 * Remove an option.
	 *
	 * @param   string  $name  Option name to remove from this set.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function offsetUnset($name)
	{
		$option = $this->findOption($name);

		if ($option instanceof Option)
		{
			unset($this->options[$option->getName()]);
		}
	}

	/**
	 * Count options.
	 *
	 * @return  integer  Number of options.
	 *
	 * @since   2.0
	 */
	public function count()
	{
		return count($this->options);
	}

	/**
	 * Serialize this object
	 *
	 * @return string
	 *
	 * @since   2.0
	 */
	public function serialize()
	{
		return serialize($this->options);
	}

	/**
	 * Un serialize this object.
	 *
	 * @param   string  $data  Serialized data.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function unserialize($data)
	{
		$this->options = unserialize($data);
	}

	/**
	 * Convert to array.
	 *
	 * @return  Option[]
	 *
	 * @since   2.0
	 */
	public function toArray()
	{
		return $this->options;
	}
}
