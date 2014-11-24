<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Cache\Item;

/**
 * Class CacheItem
 *
 * @since {DEPLOY_VERSION}
 */
class CacheItem implements CacheItemInterface
{
	/**
	 * The key for the cache item.
	 *
	 * @var    string
	 * @since  {DEPLOY_VERSION}
	 */
	private $key;

	/**
	 * The value of the cache item.
	 *
	 * @var    mixed
	 * @since  {DEPLOY_VERSION}
	 */
	private $value;

	/**
	 * Whether the cache item is value or not.
	 *
	 * @var    boolean
	 * @since  {DEPLOY_VERSION}
	 */
	private $hit;

	/**
	 * Class constructor.
	 *
	 * @param   string $key   The key for the cache item.
	 * @param   mixed  $value The value for the cache item.
	 *
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
	 */
	public function isHit()
	{
		return $this->hit;
	}
}

