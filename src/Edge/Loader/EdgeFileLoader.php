<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Edge\Loader;

use Windwalker\Cache\CacheInterface;
use Windwalker\Cache\Storage\CacheStorageInterface;

/**
 * The EdgeFileLoader class.
 *
 * @since  {DEPLOY_VERSION}
 */
class EdgeFileLoader implements EdgeLoaderInterface
{
	/**
	 * Property cache.
	 *
	 * @var  CacheInterface
	 */
	protected $cache;

	/**
	 * Property storage.
	 *
	 * @var  CacheStorageInterface
	 */
	protected $storage;

	protected $extensions = array('.edge.php', '.blade.php');

	protected $paths = array();

	public function find($key)
	{
		$key = $this->normalize($key);

		$filePath = null;

		foreach ($this->paths as $path)
		{
			foreach ($this->extensions as $ext)
			{
				if (is_file($path . '/' . $key . $ext))
				{
					$filePath = $path . '/' . $key . $ext;

					break 2;
				}
			}
		}

		if ($filePath === null)
		{
			throw new \UnexpectedValueException('File not found: ' . $key);
		}

		return $filePath;
	}

	/**
	 * loadFile
	 *
	 * @param   string  $path
	 *
	 * @return  string
	 */
	public function load($path)
	{
		return file_get_contents($path);
	}
	
	public function addPath($path)
	{
		$this->paths[] = $path;
	}

	/**
	 * normalize
	 *
	 * @param   string  $path
	 *
	 * @return  string
	 */
	protected function normalize($path)
	{
		return str_replace('.', '/', $path);
	}
}
