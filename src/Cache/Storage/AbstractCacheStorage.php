<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Cache\Storage;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

/**
 * Class AbstractCacheStorage
 *
 * @since 2.0
 */
abstract class AbstractCacheStorage implements CacheItemPoolInterface
{
	/**
	 * The Time To Live of an item.
	 *
	 * @var  integer
	 */
	protected $ttl = 900;

	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = array();

	/**
	 * Property differed.
	 *
	 * @var  CacheItemInterface[]
	 */
	protected $differed = array();

	/**
	 * Property commiting.
	 *
	 * @var  boolean
	 */
	protected $commiting = false;

	/**
	 * Constructor.
	 *
	 * @param   int    $ttl      The Time To Live (TTL) of an item
	 * @param   mixed  $options  An options array, or an object that implements \ArrayAccess
	 *
	 * @since   2.0
	 */
	public function __construct($ttl = null, $options = array())
	{
		$this->options = $options;
		$this->ttl = $ttl ? : 900;
	}

	/**
	 * getItems
	 *
	 * @param array $keys
	 *
	 * @return  \Traversable A traversable collection of Cache Items in the same order as the $keys
	 *                       parameter, keyed by the cache keys of each item. If no items are found
	 *                       an empty Traversable collection will be returned.
	 */
	public function getItems(array $keys = array())
	{
		$items = array();

		foreach ($keys as $key)
		{
			$items = $this->getItem($key);
		}

		return $items;
	}

	/**
	 * Removes multiple items from the pool.
	 *
	 * @param array $keys An array of keys that should be removed from the pool.
	 *
	 * @return static Return self to support chaining
	 */
	public function deleteItems(array $keys)
	{
		foreach ($keys as $key)
		{
			$this->deleteItems($key);
		}

		return $this;
	}

	/**
	 * Confirms if the cache contains specified cache item.
	 *
	 * Note: This method MAY avoid retrieving the cached value for performance reasons.
	 * This could result in a race condition with CacheItemInterface::get(). To avoid
	 * such situation use CacheItemInterface::isHit() instead.
	 *
	 * @param string $key
	 *    The key for which to check existence.
	 *
	 * @throws InvalidArgumentException
	 *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
	 *   MUST be thrown.
	 *
	 * @return bool
	 *  True if item exists in the cache, false otherwise.
	 */
	public function hasItem($key)
	{
		return $this->getItem($key)->isHit();
	}

	/**
	 * Confirms if the cache contains specified cache item.
	 *
	 * Note: This method MAY avoid retrieving the cached value for performance reasons.
	 * This could result in a race condition with CacheItemInterface::get(). To avoid
	 * such situation use CacheItemInterface::isHit() instead.
	 *
	 * @param string $key
	 *    The key for which to check existence.
	 *
	 * @throws InvalidArgumentException
	 *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
	 *   MUST be thrown.
	 *
	 * @return bool
	 *  True if item exists in the cache, false otherwise.
	 */
	abstract public function exists($key);

	/**
	 * Method to get property Options
	 *
	 * @return  array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Method to set property options
	 *
	 * @param   array $options
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setOptions($options)
	{
		$this->options = $options;

		return $this;
	}

	/**
	 * Sets a cache item to be persisted later.
	 *
	 * @param CacheItemInterface $item
	 *   The cache item to save.
	 *
	 * @return bool
	 *   False if the item could not be queued or if a commit was attempted and failed. True otherwise.
	 */
	public function saveDeferred(CacheItemInterface $item)
	{
		while ($this->commiting)
		{
			usleep(1);
		}

		$this->differed[$item->getKey()] = $item;

		return $this;
	}

	/**
	 * Persists any deferred cache items.
	 *
	 * @return bool
	 *   True if all not-yet-saved items were successfully saved or there were none. False otherwise.
	 */
	public function commit()
	{
		$this->commiting = true;

		foreach ($this->differed as $key => $item)
		{
			$this->save($item);

			if ($item->isHit())
			{
				unset($this->differed[$key]);
			}
		}

		$result = !count($this->differed);

		$this->commiting = false;

		return $result;
	}
}
