<?php
/**
 * Part of formosa project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Model;

use Windwalker\Registry\Registry;

/**
 * Class AbstractModel
 *
 * @since 1.0
 */
abstract class AbstractModel implements ModelInterface, \ArrayAccess
{
	/**
	 * Property cache.
	 *
	 * @var  array
	 */
	protected $cache = array();

	/**
	 * The model state.
	 *
	 * @var    Registry
	 */
	protected $state;

	/**
	 * Property magicMethodPrefix.
	 *
	 * @var  array
	 */
	protected $magicMethodPrefix = array(
		'get',
		'load'
	);

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
	 * __call
	 *
	 * @param string $name
	 * @param array  $args
	 *
	 * @return  mixed
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __call($name, $args = array())
	{
		$allow = false;

		foreach ($this->magicMethodPrefix as $prefix)
		{
			if (substr($name, 0, $prefix) == $prefix)
			{
				$allow = true;

				break;
			}
		}

		if (!$allow)
		{
			throw new \InvalidArgumentException(sprintf("Method %s::%s not found.", get_called_class(), $name));
		}

		return null;
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
	 * getStoredId
	 *
	 * @param string $id
	 *
	 * @return  string
	 */
	public function getStoredId($id = null)
	{
		$id = $id . json_encode($this->state->toArray());

		return md5($id);
	}

	/**
	 * getCache
	 *
	 * @param string $id
	 *
	 * @return  mixed
	 */
	protected function getCache($id = null)
	{
		$id = $this->getStoredId($id);

		if (empty($this->cache[$id]))
		{
			return null;
		}

		return $this->cache[$id];
	}

	/**
	 * setCache
	 *
	 * @param string $id
	 * @param mixed  $item
	 *
	 * @return  mixed
	 */
	protected function setCache($id = null, $item = null)
	{
		$id = $this->getStoredId($id);

		$this->cache[$id] = $item;

		return $item;
	}

	/**
	 * hasCache
	 *
	 * @param string $id
	 *
	 * @return  bool
	 */
	protected function hasCache($id = null)
	{
		$id = $this->getStoredId($id);

		return !empty($this->cache[$id]);
	}

	/**
	 * Whether a offset exists
	 *
	 * @param mixed $offset An offset to check for.
	 *
	 * @return boolean True on success or false on failure.
	 *                 The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset)
	{
		return (boolean) ($this->state->get($offset) !== null);
	}

	/**
	 * Offset to retrieve
	 *
	 * @param mixed $offset The offset to retrieve.
	 *
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset)
	{
		return $this->state->get($offset);
	}

	/**
	 * Offset to set
	 *
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value  The value to set.
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->state->set($offset, $value);
	}

	/**
	 * Offset to unset
	 *
	 * @param mixed $offset The offset to unset.
	 *
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		$this->state->set($offset, null);
	}
}
 