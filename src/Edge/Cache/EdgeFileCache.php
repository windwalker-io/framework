<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Edge\Cache;

/**
 * The FileCacheHandler class.
 *
 * @since  3.0-beta
 */
class EdgeFileCache implements EdgeCacheInterface
{
	/**
	 * Property path.
	 *
	 * @var  string
	 */
	protected $path;

	/**
	 * FileCacheHandler constructor.
	 *
	 * @param string $path
	 */
	public function __construct($path)
	{
		$this->path = $path;
	}

	/**
	 * isExpired
	 *
	 * @param   string  $path
	 *
	 * @return  boolean
	 */
	public function isExpired($path)
	{
		$cachePath = $this->getCacheFile($this->getCacheKey($path));

		if (!is_file($cachePath))
		{
			return true;
		}

		return filemtime($path) >= filemtime($cachePath);
	}

	/**
	 * getCacheKey
	 *
	 * @param   string $path
	 *
	 * @return  string
	 */
	public function getCacheKey($path)
	{
		return md5($path);
	}

	/**
	 * getCacheFile
	 *
	 * @param   string  $key
	 *
	 * @return  string
	 */
	public function getCacheFile($key)
	{
		return $this->path . '/~' . $key;
	}

	/**
	 * load
	 *
	 * @param string $path
	 *
	 * @return  string
	 */
	public function load($path)
	{
		return file_get_contents($this->getCacheFile($this->getCacheKey($path)));
	}

	/**
	 * store
	 *
	 * @param string $path
	 * @param string $value
	 *
	 * @return  static
	 */
	public function store($path, $value)
	{
		$file = $this->getCacheFile($this->getCacheKey($path));
		
		if (!is_dir(dirname($file)))
		{
			mkdir(dirname($file), 0755, true);
		}

		file_put_contents($file, $value);

		return $this;
	}

	/**
	 * Remove an item from the cache by its unique key
	 *
	 * @param string $path The path to remove.
	 *
	 * @return  static
	 */
	public function remove($path)
	{
		@unlink($this->getCacheFile($this->getCacheKey($path)));

		return $this;
	}
}
