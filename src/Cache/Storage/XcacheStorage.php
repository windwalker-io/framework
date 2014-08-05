<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Cache\Storage;

use Windwalker\Cache\Item\CacheItem;
use Windwalker\Cache\Item\CacheItemInterface;

/**
 * Class XcacheStorage
 *
 * @since {DEPLOY_VERSION}
 */
class XcacheStorage extends AbstractCacheStorage
{
	/**
	 * Constructor.
	 *
	 * @param   int   $ttl     The Time To Live (TTL) of an item
	 * @param   mixed $options An options array, or an object that implements \ArrayAccess
	 *
	 * @throws \RuntimeException
	 * @since   {DEPLOY_VERSION}
	 */
	public function __construct($ttl = null, $options = array())
	{
		if (!extension_loaded('xcache') || !is_callable('xcache_get'))
		{
			throw new \RuntimeException('XCache not supported.');
		}

		parent::__construct($ttl, $options);
	}

	/**
	 * Method to determine whether a storage entry has been set for a key.
	 *
	 * @param   string $key The storage entry identifier.
	 *
	 * @return  boolean
	 */
	public function exists($key)
	{
		return xcache_isset($key);
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

		if ($this->exists($key))
		{
			$item->setValue(xcache_get($key));
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
		xcache_set($item->getKey(), $item->getValue(), $ttl);

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
		xcache_unset($key);

		return $this;
	}

	/**
	 * This will wipe out the entire cache's keys
	 *
	 * @return static Return self to support chaining
	 */
	public function clear()
	{
		return $this;
	}
}

