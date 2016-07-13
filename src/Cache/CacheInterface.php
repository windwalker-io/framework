<?php

namespace Windwalker\Cache;

use Psr\Cache\CacheItemInterface;

/**
 * Interface CacheInterface
 */
interface CacheInterface
{
	/**
	 * Here we pass in a cache key to be fetched from the cache.
	 * A CacheItem object will be constructed and returned to us
	 *
	 * @param string $key The unique key of this item in the cache
	 *
	 * @return CacheItemInterface  The newly populated CacheItem class representing the stored data in the cache
	 */
	public function get($key);

	/**
	 * Persisting our data in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param string   $key The key of the item to store
	 * @param mixed    $val The value of the item to store
	 * @param int|null $ttl Optional. The TTL value of this item. If no value is sent and the driver supports TTL
	 *                      then the library may set a default value for it or let the driver take care of that.
	 *
	 * @return  CacheItemInterface  Return CacheItem to chaining.
	 */
	public function set($key, $val, $ttl = null);

	/**
	 * Remove an item from the cache by its unique key
	 *
	 * @param string $key The unique cache key of the item to remove
	 *
	 * @return  static
	 */
	public function remove($key);

	/**
	 * Fetch data from a callback if item not exists.
	 *
	 * @param string   $key      The key of the item to fetch.
	 * @param callable $callable The callback to fetch data.
	 *
	 * @throws \InvalidArgumentException
	 * @return  mixed
	 */
	public function call($key, $callable);

	/**
	 * Obtain multiple CacheItems by their unique keys
	 *
	 * @param array $keys A list of keys that can obtained in a single operation.
	 *
	 * @return array An array of CacheItem classes.
	 *               The resulting array must use the CacheItem's key as the associative key for the array.
	 */
	public function getMultiple(array $keys);

	/**
	 * Persisting a set of key => value pairs in the cache, with an optional TTL.
	 *
	 * @param array $items An array of key => value pairs for a multiple-set operation.
	 *
	 * @return static Return self to support chaining.
	 * @internal param int|null $ttl Optional. The TTL value of this item. If no value is sent and the driver supports TTL
	 *                            then the library may set a default value for it or let the driver take care of that.
	 *
	 */
	public function setMultiple(array $items);

	/**
	 * Remove multiple cache items in a single operation
	 *
	 * @param array $keys The array of keys to be removed
	 *
	 * @return static Return self to support chaining.
	 */
	public function removeMultiple(array $keys);

	/**
	 * This will wipe out the entire cache's keys
	 *
	 * @return boolean The result of the empty operation
	 */
	public function clear();

	/**
	 * exists
	 *
	 * @param string $key
	 *
	 * @return  bool
	 */
	public function exists($key);
}
