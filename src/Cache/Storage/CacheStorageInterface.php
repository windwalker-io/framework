<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Cache\Storage;

use Windwalker\Cache\Item\CacheItemInterface;

/**
 * Class CacheStorageInterface
 *
 * @since 2.0
 */
interface CacheStorageInterface
{
	/**
	 * Method to determine whether a storage entry has been set for a key.
	 *
	 * @param   string  $key  The storage entry identifier.
	 *
	 * @return  boolean
	 */
	public function exists($key);

	/**
	 * Here we pass in a cache key to be fetched from the cache.
	 * A CacheItem object will be constructed and returned to us
	 *
	 * @param string $key The unique key of this item in the cache
	 *
	 * @return CacheItemInterface  The newly populated CacheItem class representing the stored data in the cache
	 */
	public function getItem($key);

	/**
	 * Persisting our data in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param CacheItemInterface          $item The cache item to store.
	 * @param int|\DateInterval|\DateTime $ttl  The Time To Live of an item.
	 *
	 * @return static Return self to support chaining
	 */
	public function setItem($item, $ttl = null);

	/**
	 * Remove an item from the cache by its unique key
	 *
	 * @param string $key The unique cache key of the item to remove
	 *
	 * @return static Return self to support chaining
	 */
	public function removeItem($key);

	/**
	 * getItems
	 *
	 * @param array $keys
	 *
	 * @return  \Traversable A traversable collection of Cache Items in the same order as the $keys
	 *                       parameter, keyed by the cache keys of each item. If no items are found
	 *                       an empty Traversable collection will be returned.
	 */
	public function getItems(array $keys);

	/**
	 * setItems
	 *
	 * @param array $items
	 *
	 * @return  static Return self to support chaining
	 */
	public function setItems(array $items);

	/**
	 * Removes multiple items from the pool.
	 *
	 * @param array $keys An array of keys that should be removed from the pool.
	 *
	 * @return static Return self to support chaining
	 */
	public function removeItems(array $keys);

	/**
	 * This will wipe out the entire cache's keys
	 *
	 * @return static Return self to support chaining
	 */
	public function clear();
}

