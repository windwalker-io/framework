<?php
/**
 * Part of formosa project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Cache;

use Windwalker\Cache\DataHandler\DataHandlerInterface;
use Windwalker\Cache\DataHandler\SerializeHandler;
use Windwalker\Cache\Item\CacheItem;
use Windwalker\Cache\Item\CacheItemInterface;
use Windwalker\Cache\Storage\CacheStorageInterface;
use Windwalker\Cache\Storage\RuntimeStorage;

/**
 * Class Cache
 *
 * @since 1.0
 */
class Cache implements CacheInterface
{
	/**
	 * Property storage.
	 *
	 * @var  CacheStorageInterface
	 */
	protected $storage = null;
	/**
	 * Property handler.
	 *
	 * @var  null
	 */
	protected $handler = null;

	/**
	 * Class init.
	 *
	 * @param CacheStorageInterface $storage
	 * @param DataHandlerInterface  $handler
	 */
	public function __construct(CacheStorageInterface $storage = null, DataHandlerInterface $handler = null)
	{
		$this->storage = $storage ? : new RuntimeStorage;
		$this->handler = $handler ? : new SerializeHandler;
	}

	/**
	 * Here we pass in a cache key to be fetched from the cache.
	 * A CacheItem object will be constructed and returned to us
	 *
	 * @param string $key The unique key of this item in the cache
	 *
	 * @return CacheItemInterface  The newly populated CacheItem class representing the stored data in the cache
	 */
	public function get($key)
	{
		$value = $this->storage->getItem($key)->getValue();

		return $this->handler->decode($value);
	}

	/**
	 * Persisting our data in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param string       $key The key of the item to store
	 * @param mixed        $val The value of the item to store
	 * @param null|integer $ttl Optional. The TTL value of this item. If no value is sent and the driver supports TTL
	 *                          then the library may set a default value for it or let the driver take care of that.
	 *
	 * @return boolean
	 */
	public function set($key, $val, $ttl = null)
	{
		$item = new CacheItem($key, $this->handler->encode($val));

		$this->storage->setItem($item, $ttl);

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
		$this->storage->removeItem($key);

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
	 * @param array        $items An array of key => value pairs for a multiple-set operation.
	 * @param null|integer $ttl   Optional. The TTL value of this item. If no value is sent and the driver supports TTL
	 *                            then the library may set a default value for it or let the driver take care of that.
	 *
	 * @return static Return self to support chaining.
	 */
	public function setMultiple(array $items, $ttl = null)
	{
		$this->storage->setItems($items, $ttl);

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
		$this->storage->removeItems($keys);

		return $this;
	}

	/**
	 * getStorage
	 *
	 * @return  \Windwalker\Cache\Storage\CacheStorageInterface
	 */
	public function getStorage()
	{
		return $this->storage;
	}

	/**
	 * setStorage
	 *
	 * @param   \Windwalker\Cache\Storage\CacheStorageInterface $storage
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
		return $this->storage->exists($key);
	}

	/**
	 * getHandler
	 *
	 * @return  null
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * setHandler
	 *
	 * @param   null $handler
	 *
	 * @return  Cache  Return self to support chaining.
	 */
	public function setHandler($handler)
	{
		$this->handler = $handler;

		return $this;
	}
}

