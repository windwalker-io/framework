<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Cache\Storage;

use Psr\Cache\CacheItemInterface;
use Windwalker\Cache\Item\CacheItem;

/**
 * Runtime Storage.
 *
 * @since 2.0
 */
class ArrayStorage extends AbstractCacheStorage
{
    /**
     * Property storage.
     *
     * @var  array
     */
    protected $data = [];

    /**
     * Method to determine whether a storage entry has been set for a key.
     *
     * @param   string $key The storage entry identifier.
     *
     * @return  boolean
     */
    public function exists($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Here we pass in a cache key to be fetched from the cache.
     * A CacheItem object will be constructed and returned to us
     *
     * @param string $key The unique key of this item in the cache
     *
     * @return CacheItemInterface  The newly populated CacheItem class representing the stored data in the cache
     * @throws \Exception
     */
    public function getItem($key)
    {
        if (!$this->exists($key)) {
            return new CacheItem($key);
        }

        $item = new CacheItem($key);

        if (isset($this->data[$key])) {
            $item->set($this->data[$key]);
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
        $key = $item->getKey();
        $value = $item->get();

        $this->data[$key] = $value;

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
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
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
        $this->data = [];

        return $this;
    }

    /**
     * Method to get property Data
     *
     * @return  array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Method to set property data
     *
     * @param   array $data
     *
     * @return  static  Return self to support chaining.
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
