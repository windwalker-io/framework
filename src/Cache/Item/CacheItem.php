<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Cache\Item;

use Psr\Cache\CacheItemInterface;

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
	protected $key;

	/**
	 * The value of the cache item.
	 *
	 * @var    mixed
	 * @since  2.0
	 */
	protected $value;

	/**
	 * Whether the cache item is value or not.
	 *
	 * @var    boolean
	 * @since  2.0
	 */
	protected $hit;

	/**
	 * Property expiration.
	 *
	 * @var  \DateTimeInterface
	 */
	protected $expiration;

	/**
	 * Property defaultExpiration.
	 *
	 * @var  string
	 */
	protected $defaultExpiration = 'now +1 year';

	/**
	 * Class constructor.
	 *
	 * @param   string             $key   The key for the cache item.
	 * @param   mixed              $value The value for the cache item.
	 * @param   \DateInterval|int  $ttl   The expire time.
	 *
	 * @since   2.0
	 */
	public function __construct($key, $value = null, $ttl = null)
	{
		if (strpbrk($key, '{}()/\@:'))
		{
			throw new \InvalidArgumentException('Item key name contains invalid characters.' . $key);
		}

		$this->key = $key;

		if ($value !== null)
		{
			$this->set($value);
		}

		$this->expiresAfter($ttl);
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
	public function get()
	{
		if ($this->isHit() === false)
		{
			return null;
		}

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
	public function set($value)
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
		if (new \DateTime > $this->expiration)
		{
			$this->hit = false;
		}

		return $this->hit;
	}

	/**
	 * Sets the expiration time for this cache item.
	 *
	 * @param \DateTimeInterface $expiration
	 *   The point in time after which the item MUST be considered expired.
	 *   If null is passed explicitly, a default value MAY be used. If none is set,
	 *   the value should be stored permanently or for as long as the
	 *   implementation allows.
	 *
	 * @return static
	 *   The called object.
	 */
	public function expiresAt($expiration)
	{
		$tzBackup = @date_default_timezone_get();
		date_default_timezone_set('UTC');

		if ($expiration instanceof \DateTimeInterface)
		{
			$this->expiration = $expiration;

		}
		elseif ($expiration === null)
		{
			$this->expiration = new \DateTime($this->defaultExpiration);
		}
		else
		{
			throw new \InvalidArgumentException('Invalid DateTime format.');
		}

		date_default_timezone_set($tzBackup);

		return $this;
	}

	/**
	 * Sets the expiration time for this cache item.
	 *
	 * @param int|\DateInterval $time
	 *   The period of time from the present after which the item MUST be considered
	 *   expired. An integer parameter is understood to be the time in seconds until
	 *   expiration. If null is passed explicitly, a default value MAY be used.
	 *   If none is set, the value should be stored permanently or for as long as the
	 *   implementation allows.
	 *
	 * @return static
	 *   The called object.
	 */
	public function expiresAfter($time)
	{
		$tzBackup = @date_default_timezone_get();
		date_default_timezone_set('UTC');

		if ($time instanceof \DateInterval)
		{
			$this->expiration = new \DateTime;
			$this->expiration->add($time);
		}
		elseif (is_int($time))
		{
			$this->expiration = new \DateTime;
			$this->expiration->add(new \DateInterval('PT' . $time . 'S'));
		}
		elseif ($time === null)
		{
			$this->expiration = new \DateTime($this->defaultExpiration);
		}
		else
		{
			throw new \InvalidArgumentException('Invalid DateTime format.');
		}

		date_default_timezone_set($tzBackup);

		return $this;
	}

	/**
	 * Method to get property Expiration
	 *
	 * @return  \DateTimeInterface
	 */
	public function getExpiration()
	{
		return $this->expiration;
	}
}
