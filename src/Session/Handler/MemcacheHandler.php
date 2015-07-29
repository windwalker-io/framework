<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Session\Handler;

/**
 * Class MemcacheHandler
 *
 * @since 2.0
 */
class MemcacheHandler extends AbstractHandler
{
	/**
	 * Property memcache.
	 *
	 * @var \Memcache
	 */
	protected $memcache;

	/**
	 * Property ttl.
	 *
	 * @var  integer
	 */
	protected $ttl = null;

	/**
	 * Constructor
	 *
	 * @param   \Memcache $memcache A Memcache instance
	 * @param   array     $options  Optional parameters.
	 *
	 * @throws \RuntimeException
	 * @since   2.0
	 */
	public function __construct(\Memcache $memcache = null, $options = array())
	{
		if (!self::isSupported())
		{
			throw new \RuntimeException('Memcache Extension is not available', 500);
		}

		$this->memcache = $memcache ? : new \Memcache;

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
		return (extension_loaded('memcache') && class_exists('Memcache'));
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
		$this->memcache->close();

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
		return $this->memcache->get($this->prefix.$id) ?: '';
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
		return $this->memcache->set($this->prefix.$id, $data, 0, time() + $this->ttl);
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
		return $this->memcache->delete($this->prefix.$id);
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
		// not required here because memcache will auto expire the records anyhow.
		return true;
	}

	/**
	 * Return a Memcache instance
	 *
	 * @return \Memcache
	 */
	protected function getMemcache()
	{
		return $this->memcache;
	}
}

