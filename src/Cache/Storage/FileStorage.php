<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Cache\Storage;

use Windwalker\Cache\Item\CacheItem;
use Windwalker\Cache\Item\CacheItemInterface;

/**
 * Filesystem cache driver for the Windwalker Framework.
 *
 * Supported options:
 * - ttl (integer)          : The default number of seconds for the cache life.
 * - file.locking (boolean) :
 * - file.path              : The path for cache files.
 *
 * @since  2.0
 */
class FileStorage extends AbstractCacheStorage
{
	/**
	 * Property path.
	 *
	 * @var  string
	 */
	protected $path = null;

	/**
	 * Property group.
	 *
	 * @var  string
	 */
	protected $group = '';

	/**
	 * Property denyAccess.
	 *
	 * @var  bool
	 */
	protected $denyAccess = false;

	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = array(
		'format' => '.data',
		'file_locking' => true,
		'deny_code' => '<?php die("Access Deny"); ?>'
	);

	/**
	 * Constructor.
	 *
	 * @param   int     $path
	 * @param   string  $group
	 * @param   bool    $denyAccess
	 * @param   int     $ttl
	 * @param   mixed   $options An options array, or an object that implements \ArrayAccess
	 *
	 * @since   2.0
	 */
	public function __construct($path, $group = '', $denyAccess = false, $ttl = null, $options = array())
	{
		$this->path = $path;
		$this->group = $group;
		$this->denyAccess = $denyAccess;

		$options = array_merge($this->options, $options);

		$this->checkFilePath($path);

		parent::__construct($ttl, $options);
	}

	/**
	 * This will wipe out the entire cache's keys....
	 *
	 * @return  boolean  The result of the clear operation.
	 *
	 * @since   2.0
	 */
	public function clear()
	{
		$filePath = $this->path;
		$this->checkFilePath($filePath);

		$iterator = new \RegexIterator(
			new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($filePath)
			),
			'/' . preg_quote($this->options['format']) . '$/i'
		);

		/* @var  \RecursiveDirectoryIterator  $file */
		foreach ($iterator as $file)
		{
			if ($file->isFile())
			{
				@unlink($file->getRealPath());
			}
		}

		return true;
	}

	/**
	 * Method to get a storage entry value from a key.
	 *
	 * @param   string  $key  The storage entry identifier.
	 *
	 * @return  CacheItemInterface
	 *
	 * @since   2.0
	 * @throws  \RuntimeException
	 */
	public function getItem($key)
	{
		if (!$this->exists($key))
		{
			return new CacheItem($key);
		}

		$resource = @fopen($this->fetchStreamUri($key), 'rb');

		if (!$resource)
		{
			throw new \RuntimeException(sprintf('Unable to fetch cache entry for %s.  Connot open the resource.', $key));
		}

		// If locking is enabled get a shared lock for reading on the resource.
		if ($this->options['file_locking'] && !flock($resource, LOCK_SH))
		{
			throw new \RuntimeException(sprintf('Unable to fetch cache entry for %s.  Connot obtain a lock.', $key));
		}

		$data = stream_get_contents($resource);

		// If locking is enabled release the lock on the resource.
		if ($this->options['file_locking'] && !flock($resource, LOCK_UN))
		{
			throw new \RuntimeException(sprintf('Unable to fetch cache entry for %s.  Connot release the lock.', $key));
		}

		fclose($resource);

		$item = new CacheItem($key);

		if ($this->denyAccess)
		{
			$data = substr($data, strlen($this->options['deny_code']));
		}

		$item->setValue($data);

		return $item;
	}

	/**
	 * Method to remove a storage entry for a key.
	 *
	 * @param   string  $key  The storage entry identifier.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.0
	 */
	public function removeItem($key)
	{
		return (bool) @unlink($this->fetchStreamUri($key));
	}

	/**
	 * Persisting our data in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param CacheItem                   $item The cache item to store.
	 * @param int|\DateInterval|\DateTime $ttl  The Time To Live of an item.
	 *
	 * @return static Return self to support chaining
	 */
	public function setItem($item, $ttl = null)
	{
		$key = $item->getKey();
		$value = $item->getValue();

		$fileName = $this->fetchStreamUri($key);

		$filePath = pathinfo($fileName, PATHINFO_DIRNAME);

		if (!is_dir($filePath))
		{
			mkdir($filePath, 0770, true);
		}

		if ($this->denyAccess)
		{
			$value = $this->options['deny_code'] . $value;
		}

		$success = (bool) file_put_contents(
			$fileName,
			$value,
			($this->options['file_locking'] ? LOCK_EX : null)
		);

		return $success;
	}

	/**
	 * Method to determine whether a storage entry has been set for a key and not expired.
	 *
	 * @param   string  $key  The storage entry identifier.
	 *
	 * @return  boolean  Tue of exists and not expired.
	 *
	 * @since   2.0
	 */
	public function exists($key)
	{
		if (is_file($this->fetchStreamUri($key)))
		{
			if ($this->isExpired($key))
			{
				try
				{
					$this->removeItem($key);
				}
				catch (\RuntimeException $e)
				{
					throw new \RuntimeException(sprintf('Unable to clean expired cache entry for %s.', $key), null, $e);
				}

				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * Check that the file path is a directory and writable.
	 *
	 * @param   string  $filePath  A file path.
	 *
	 * @return  boolean  The method will always return true, if it returns.
	 *
	 * @since   2.0
	 * @throws  \RuntimeException if the file path is invalid.
	 */
	protected function checkFilePath($filePath)
	{
		if (!is_dir($filePath))
		{
			mkdir($filePath, 0755, true);
		}

		if (!is_writable($filePath))
		{
			throw new \RuntimeException(sprintf('The base cache path `%s` is not writable.', $filePath));
		}

		return true;
	}

	/**
	 * Get the full stream URI for the cache entry.
	 *
	 * @param   string  $key  The storage entry identifier.
	 *
	 * @return  string  The full stream URI for the cache entry.
	 *
	 * @since   2.0
	 * @throws  \RuntimeException if the cache path is invalid.
	 */
	protected function fetchStreamUri($key)
	{
		$filePath = $this->path;

		$this->checkFilePath($filePath);

		if ($this->denyAccess)
		{
			$this->options['format'] = '.php';
		}

		return sprintf(
			'%s/%s~%s' . $this->options['format'],
			$filePath,
			($this->group) ? $this->group . '/' : $this->group,
			hash('sha1', $key)
		);
	}

	/**
	 * Check whether or not the cached data by id has expired.
	 *
	 * @param   string  $key  The storage entry identifier.
	 *
	 * @return  boolean  True if the data has expired.
	 *
	 * @since   2.0
	 */
	public function isExpired($key)
	{
		// Check to see if the cached data has expired.
		if (filemtime($this->fetchStreamUri($key)) < (time() - $this->ttl))
		{
			return true;
		}

		return false;
	}

	/**
	 * getDenyAccess
	 *
	 * @param boolean $bool
	 *
	 * @return  boolean
	 */
	public function denyAccess($bool = null)
	{
		if ($bool === null)
		{
			return $this->denyAccess;
		}

		return $this->denyAccess = $bool;
	}

	/**
	 * Method to get property Group
	 *
	 * @return  string
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * Method to set property group
	 *
	 * @param   string $group
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setGroup($group)
	{
		$this->group = $group;

		return $this;
	}

	/**
	 * Method to get property Path
	 *
	 * @return  string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Method to set property path
	 *
	 * @param   string $path
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setPath($path)
	{
		$this->path = $path;

		return $this;
	}
}

