<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Model;

use Windwalker\Registry\Registry;

/**
 * Class AbstractModel
 *
 * @since {DEPLOY_VERSION}
 */
abstract class AbstractModel implements ModelInterface, \ArrayAccess
{
	/**
	 * The model state.
	 *
	 * @var  Registry
	 */
	protected $state;

	/**
	 * Instantiate the model.
	 *
	 * @param   Registry  $state  The model state.
	 */
	public function __construct(Registry $state = null)
	{
		$this->state = ($state instanceof Registry) ? $state : new Registry;
	}

	/**
	 * Get the model state.
	 *
	 * @return  Registry  The state object.
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Set the model state.
	 *
	 * @param   Registry  $state  The state object.
	 *
	 * @return  void
	 */
	public function setState(Registry $state)
	{
		$this->state = $state;
	}

	/**
	 * get
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return  mixed
	 */
	public function get($key, $default = null)
	{
		return $this->state->get($key, $default);
	}

	/**
	 * set
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return  $this
	 */
	public function set($key, $value)
	{
		$this->state->set($key, $value);

		return $this;
	}

	/**
	 * reset
	 *
	 * @return  static
	 */
	public function reset()
	{
		$this->state->clear();

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
		return $this->state->exists($offset);
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
		return $this->state->get($offset);
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
		$this->state->set($offset, $value);
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
		$this->state->set($offset, null);
	}
}
