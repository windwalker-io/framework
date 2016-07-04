<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Windwalker\Cache\Serializer\SerializerInterface;
use Windwalker\Cache\Serializer\PhpSerializer;
use Windwalker\Cache\Item\CacheItem;
use Windwalker\Cache\Storage\ArrayStorage;

/**
 * Class Cache
 *
 * @since 2.0
 */
class Cache implements CacheInterface, \ArrayAccess
{
	/**
	 * Property storage.
	 *
	 * @var  CacheItemPoolInterface
	 */
	protected $storage = null;

	/**
	 * Property handler.
	 *
	 * @var  SerializerInterface
	 */
	protected $serializer = null;

	/**
	 * Class init.
	 *
	 * @param CacheItemPoolInterface $storage
	 * @param SerializerInterface    $serializer
	 */
	public function __construct(CacheItemPoolInterface $storage = null, SerializerInterface $serializer = null)
	{
		$this->storage    = $storage ?: new ArrayStorage;
		$this->serializer = $serializer ?: new PhpSerializer;
	}

	/**
	 * Here we pass in a cache key to be fetched from the cache.
	 * A CacheItem object will be constructed and returned to us
	 *
	 * @param   string  $key  The unique key of this item in the cache
	 *
	 * @return  mixed  The cached value or null if not exists.
	 *
	 * @since   2.0
	 */
	public function get($key)
	{
		$value = $this->storage->getItem($key)->get();

		if ($value === null)
		{
			return $value;
		}

		return $this->serializer->unserialize($value);
	}

	/**
	 * Persisting our data in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param string $key The key of the item to store
	 * @param mixed  $val The value of the item to store
	 *
	 * @return bool
	 */
	public function set($key, $val)
	{
		$item = new CacheItem($key);

		$item->set($this->serializer->serialize($val));

		$this->storage->save($item);

		return $this;
	}

	/**
	 * Remove an item from the cache by its unique key
	 *
	 * @param string $key The unique cache key of the item to remove
	 *
	 * @return boolean    The result of the delete operation
	 */
	public function remove($key)
	{
		$this->storage->deleteItem($key);

		return $this;
	}

	/**
	 * This will wipe out the entire cache's keys
	 *
	 * @return boolean The result of the empty operation
	 */
	public function clear()
	{
		$this->storage->clear();
	}

	/**
	 * Obtain multiple CacheItems by their unique keys
	 *
	 * @param array $keys A list of keys that can obtained in a single operation.
	 *
	 * @return array An array of CacheItem classes.
	 *               The resulting array must use the CacheItem's key as the associative key for the array.
	 */
	public function getMultiple(array $keys)
	{
		return $this->storage->getItems($keys);
	}

	/**
	 * Persisting a set of key => value pairs in the cache, with an optional TTL.
	 *
	 * @param array $items An array of key => value pairs for a multiple-set operation.
	 *
	 * @return static Return self to support chaining.
	 */
	public function setMultiple(array $items)
	{
		foreach ($items as $item)
		{
			$this->storage->save($item);
		}

		return $this;
	}

	/**
	 * Remove multiple cache items in a single operation
	 *
	 * @param array $keys The array of keys to be removed
	 *
	 * @return static Return self to support chaining.
	 */
	public function removeMultiple(array $keys)
	{
		$this->storage->deleteItems($keys);

		return $this;
	}

	/**
	 * Fetch data from a callback if item not exists.
	 *
	 * @param string   $key      The key of the item to fetch.
	 * @param callable $callable The callback to fetch data.
	 *
	 * @throws \InvalidArgumentException
	 * @return  mixed
	 */
	public function call($key, $callable)
	{
		if (!is_callable($callable))
		{
			throw new \InvalidArgumentException('Not a valid callable.');
		}

		if ($this->exists($key))
		{
			return $this->get($key);
		}
		
		$args = func_get_args();
		array_shift($args);
		array_shift($args);

		$value = call_user_func_array($callable, $args);

		$this->set($key, $value);

		return $value;
	}

	/**
	 * getStorage
	 *
	 * @return  CacheItemPoolInterface
	 */
	public function getStorage()
	{
		return $this->storage;
	}

	/**
	 * setStorage
	 *
	 * @param   CacheItemPoolInterface $storage
	 *
	 * @return  Cache  Return self to support chaining.
	 */
	public function setStorage($storage)
	{
		$this->storage = $storage;

		return $this;
	}

	/**
	 * exists
	 *
	 * @param string $key
	 *
	 * @return  bool
	 */
	public function exists($key)
	{
		return $this->storage->hasItem($key);
	}

	/**
	 * getHandler
	 *
	 * @return  null
	 */
	public function getSerializer()
	{
		return $this->serializer;
	}

	/**
	 * setHandler
	 *
	 * @param   SerializerInterface $serializer
	 *
	 * @return  Cache  Return self to support chaining.
	 */
	public function setSerializer($serializer)
	{
		$this->serializer = $serializer;

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
		return $this->exists($offset);
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
		return $this->get($offset);
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
		$this->set($offset, $value);
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
		$this->remove($offset);
	}
}
