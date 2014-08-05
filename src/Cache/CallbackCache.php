<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Cache;

use Windwalker\Cache\Item\CacheItem;
use Windwalker\Cache\Item\CacheItemInterface;

/**
 * Class CallbackCache
 *
 * @since {DEPLOY_VERSION}
 */
class CallbackCache extends Cache
{
	/**
	 * call
	 *
	 * @param string   $key
	 * @param callable $callable
	 * @param array    $args
	 *
	 * @throws \InvalidArgumentException
	 * @return  mixed
	 */
	public function call($key, $callable, $args = array())
	{
		$args = (array) $args;

		if (!is_callable($callable))
		{
			throw new \InvalidArgumentException('Not a valid callable.');
		}

		if ($this->storage->exists($key))
		{
			return $this->get($key);
		}

		$value = call_user_func_array($callable, $args);

		$this->set($key, $value);

		return $value;
	}
}

