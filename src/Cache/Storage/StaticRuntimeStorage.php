<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Cache\Storage;

use Windwalker\Cache\Item\CacheItem;
use Windwalker\Cache\Item\CacheItemInterface;

/**
 * StaticRuntime Storage.
 *
 * @since 2.0
 */
class StaticRuntimeStorage extends AbstractCacheStorage
{
	/**
	 * Property storage.
	 *
	 * @var  array
	 */
	protected static $store = array();

	/**
	 * Method to determine whether a storage entry has been set for a key.
	 *
	 * @param   string $key The storage entry identifier.
	 *
	 * @return  boolean
	 */
	public function exists($key)
	{
		return isset(static::$store[$key]);
	}

	/**
	 * Here we pass in a cache key to be fetched from the cache.
	 * A CacheItem object will be constructed and returned to us
	 *
	 * @param string $key The unique key of this item in the cache
	 *
	 * @return CacheItemInterface  The newly populated CacheItem class representing the stored data in the cache
	 */
	public function getItem($key)
	{
		$item = new CacheItem($key);

		if (isset(static::$store[$key]))
		{
			$item->setValue(static::$store[$key]);
		}

		return $item;
	}

	/**
	 * Persisting our data in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param CacheItemInterface          $item The cache item to store.
	 * @param int|\DateInterval|\DateTime $ttl  The Time To Live of an item.
	 *
	 * @return static Return self to support chaining
	 */
	public function setItem($item, $ttl = null)
	{
		$key = $item->getKey();
		$value = $item->getValue();

		static::$store[$key] = $value;

		return $this;
	}

	/**
	 * Remove an item from the cache by its unique key
	 *
	 * @param string $key The unique cache key of the item to remove
	 *
	 * @return static Return self to support chaining
	 */
	public function removeItem($key)
	{
		if (array_key_exists($key, static::$store))
		{
			unset(static::$store[$key]);
		}

		return $this;
	}

	/**
	 * This will wipe out the entire cache's keys
	 *
	 * @return static Return self to support chaining
	 */
	public function clear()
	{
		static::$store = array();

		return $this;
	}
}
