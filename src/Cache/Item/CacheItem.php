<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Cache\Item;

/**
 * Class CacheItem
 *
 * @since 2.0
 */
class CacheItem implements CacheItemInterface
{
	/**
	 * The key for the cache item.
	 *
	 * @var    string
	 * @since  2.0
	 */
	private $key;

	/**
	 * The value of the cache item.
	 *
	 * @var    mixed
	 * @since  2.0
	 */
	private $value;

	/**
	 * Whether the cache item is value or not.
	 *
	 * @var    boolean
	 * @since  2.0
	 */
	private $hit;

	/**
	 * Class constructor.
	 *
	 * @param   string $key   The key for the cache item.
	 * @param   mixed  $value The value for the cache item.
	 *
	 * @since   2.0
	 */
	public function __construct($key, $value = null)
	{
		$this->key = $key;
		$this->value = $value;
		$this->hit = false;
	}

	/**
	 * Get the key associated with this CacheItem.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * Obtain the value of this cache item.
	 *
	 * @return  mixed
	 *
	 * @since   2.0
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Set the value of the item.
	 *
	 * If the value is set, we are assuming that there was a valid hit on the cache for the given key.
	 *
	 * @param   mixed  $value  The value for the cache item.
	 *
	 * @return  CacheItem
	 */
	public function setValue($value)
	{
		$this->value = $value;
		$this->hit = true;

		return $this;
	}

	/**
	 * This boolean value tells us if our cache item is currently in the cache or not.
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function isHit()
	{
		return $this->hit;
	}
}

