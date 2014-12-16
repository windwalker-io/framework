<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Session\Handler;

/**
 * Class MemcachedHandler
 *
 * @since 2.0
 */
class MemcachedHandler extends AbstractHandler
{
	/**
	 * Property memcache.
	 *
	 * @var \Memcached
	 */
	protected $memcached;

	/**
	 * Property ttl.
	 *
	 * @var  integer
	 */
	protected $ttl = null;

	/**
	 * Constructor
	 *
	 * @param   \Memcached $memcached A Memcached instance
	 * @param   array      $options  Optional parameters.
	 *
	 * @throws \RuntimeException
	 * @since   2.0
	 */
	public function __construct(\Memcached $memcached = null, $options = array())
	{
		if (!self::isSupported())
		{
			throw new \RuntimeException('Memcache Extension is not available', 500);
		}

		$this->memcached = $memcached ? : new \Memcached;

		$this->ttl = isset($options['expiretime']) ? (int) $options['expiretime'] : 86400;

		parent::__construct($options);
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 *
	 * @since   2.0
	 */
	static public function isSupported()
	{
		return (extension_loaded('memcached') && class_exists('Memcached'));
	}

	/**
	 * open
	 *
	 * @param string $savePath
	 * @param string $sessionName
	 *
	 * @return  bool
	 */
	public function open($savePath, $sessionName)
	{
		return true;
	}

	/**
	 * close
	 *
	 * @return  bool
	 */
	public function close()
	{
		return true;
	}

	/**
	 * read
	 *
	 * @param string $id
	 *
	 * @return  string
	 */
	public function read($id)
	{
		return $this->memcached->get($this->prefix.$id) ?: '';
	}

	/**
	 * write
	 *
	 * @param string $id
	 * @param string $data
	 *
	 * @return  bool
	 */
	public function write($id, $data)
	{
		return $this->memcached->set($this->prefix.$id, $data, 0, time() + $this->ttl);
	}

	/**
	 * destroy
	 *
	 * @param int|string $id
	 *
	 * @return  bool
	 */
	public function destroy($id)
	{
		return $this->memcached->delete($this->prefix.$id);
	}

	/**
	 * gc
	 *
	 * @param int|string $maxlifetime
	 *
	 * @return  bool
	 */
	public function gc($maxlifetime)
	{
		// not required here because memcached will auto expire the records anyhow.
		return true;
	}

	/**
	 * Return a Memcache instance
	 *
	 * @return \Memcache
	 */
	protected function getMemcached()
	{
		return $this->memcached;
	}
}

