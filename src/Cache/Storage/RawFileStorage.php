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
 * Filesystem cache driver.
 *
 * Supported options:
 * - ttl (integer)          : The default number of seconds for the cache life.
 * - file.locking (boolean) :
 * - file.path              : The path for cache files.
 *
 * @since  2.0
 */
class RawFileStorage extends FileStorage
{
	/**
	 * unserialize
	 *
	 * @param string $data
	 *
	 * @return  mixed|string
	 */
	protected function unserialize($data)
	{
		return $data;
	}

	/**
	 * serialize
	 *
	 * @param mixed $data
	 *
	 * @throws  \InvalidArgumentException
	 * @return  mixed|string
	 */
	protected function serialize($data)
	{
		if (is_array($data) || (is_object($data) && !method_exists($data, '_toString')))
		{
			throw new \InvalidArgumentException(__CLASS__ . ' can not handle an array or non-stringable object.');
		}

		return $data;
	}
}

