<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Cache\Storage;

use Psr\Cache\CacheItemInterface;
use Windwalker\Cache\Item\CacheItem;

/**
 * Class RedisStorage
 *
 * @since 2.0
 */
class RedisStorage extends AbstractDriverCacheStorage
{
	/**
	 * Property defaultHost.
	 *
	 * @var  string
	 */
	protected $defaultHost = '127.0.0.1';

	/**
	 * Property defaultPort.
	 *
	 * @var  int
	 */
	protected $defaultPort = 6379;

	/**
	 * Class init.
	 *
	 * @param   \Memcached $driver  The cache storage driver.
	 * @param   int        $ttl     The Time To Live (TTL) of an item
	 * @param   mixed      $options An options array, or an object that implements \ArrayAccess
	 *
	 * @throws \RuntimeException
	 */
	public function __construct($driver = null, $ttl = null, $options = array())
	{
		if (!extension_loaded('redis') || !class_exists('\Redis'))
		{
			throw new \RuntimeException('Redis not supported.');
		}

		parent::__construct($driver, $ttl, $options);
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
		$this->connect();

		return $this->driver->exists($key);
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
		if (!$this->exists($key))
		{
			return new CacheItem($key);
		}
		
		$this->connect();

		$value = $this->driver->get($key);

		$item = new CacheItem($key);

		if ($value !== false)
		{
			$item->set($value);
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
	public function save(CacheItemInterface $item, $ttl = null)
	{
		$this->connect();

		if (!$this->driver->set($item->getKey(), $item->get()))
		{
			return false;
		}

		if ($ttl)
		{
			$this->driver->expire($item->getKey(), $ttl);
		}

		return $this;
	}

	/**
	 * Remove an item from the cache by its unique key
	 *
	 * @param string $key The unique cache key of the item to remove
	 *
	 * @return static Return self to support chaining
	 */
	public function deleteItem($key)
	{
		$this->connect();

		$this->driver->del($key);

		return $this;
	}

	/**
	 * This will wipe out the entire cache's keys
	 *
	 * @return static Return self to support chaining
	 */
	public function clear()
	{
		$this->connect();

		return $this->driver->flushall();
	}

	/**
	 * connect
	 *
	 * @return  static
	 */
	protected function connect()
	{
		// We want to only create the driver once.
		if (isset($this->driver))
		{
			return $this;
		}

		if (($this->defaultHost == 'localhost' || filter_var($this->defaultHost, FILTER_VALIDATE_IP)))
		{
			$this->driver->connect('tcp://' . $this->defaultHost . ':' . $this->defaultPort, $this->defaultPort);
		}
		else
		{
			$this->driver->connect($this->defaultHost, null);
		}

		return $this;
	}
}

