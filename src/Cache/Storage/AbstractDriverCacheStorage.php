<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Cache\Storage;

/**
 * Class AbstractDriverCacheStorage
 *
 * @since {DEPLOY_VERSION}
 */
abstract class AbstractDriverCacheStorage extends AbstractCacheStorage
{
	/**
	 * Property driver.
	 *
	 * @var  \Memcached
	 */
	protected $driver = null;

	/**
	 * Constructor.
	 *
	 * @param   object $driver   The cache storage driver.
	 * @param   int    $ttl      The Time To Live (TTL) of an item
	 * @param   mixed  $options  An options array, or an object that implements \ArrayAccess
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function __construct($driver = null, $ttl = null, $options = array())
	{
		$this->driver = $driver;

		parent::__construct($ttl, $options);
	}

	/**
	 * connect
	 *
	 * @return  static
	 */
	abstract protected function connect();

	/**
	 * getDriver
	 *
	 * @return  object
	 */
	public function getDriver()
	{
		return $this->driver;
	}

	/**
	 * setDriver
	 *
	 * @param   object $driver
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDriver($driver)
	{
		$this->driver = $driver;

		return $this;
	}
}

