<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Cache\Storage;

use Windwalker\Cache\Item\CacheItem;
use Windwalker\Cache\Item\CacheItemInterface;

/**
 * Filesystem cache driver for the Joomla Framework.
 *
 * Supported options:
 * - ttl (integer)          : The default number of seconds for the cache life.
 * - file.locking (boolean) :
 * - file.path              : The path for cache files.
 *
 * @since  {DEPLOY_VERSION}
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

