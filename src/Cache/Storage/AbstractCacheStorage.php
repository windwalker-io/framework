<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Cache\Storage;

/**
 * Class AbstractCacheStorage
 *
 * @since 2.0
 */
abstract class AbstractCacheStorage implements CacheStorageInterface
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
	public function getItems(array $keys)
	{
		$items = array();

		foreach ($keys as $key)
		{
			$items = $this->getItem($key);
		}

		return $items;
	}

	/**
	 * setItems
	 *
	 * @param array $items
	 *
	 * @return  static Return self to support chaining
	 */
	public function setItems(array $items)
	{
		foreach ($items as $key => $item)
		{
			$this->setItem($key, $item);
		}

		return $this;
	}

	/**
	 * Removes multiple items from the pool.
	 *
	 * @param array $keys An array of keys that should be removed from the pool.
	 *
	 * @return static Return self to support chaining
	 */
	public function removeItems(array $keys)
	{
		foreach ($keys as $key)
		{
			$this->removeItems($key);
		}

		return $this;
	}
}

