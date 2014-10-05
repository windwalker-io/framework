<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Cache\DataHandler;

/**
 * Class SerializeHandler
 *
 * @since {DEPLOY_VERSION}
 */
class SerializeHandler implements DataHandlerInterface
{
	/**
	 * unserialize
	 *
	 * @param string $data
	 *
	 * @return  mixed
	 */
	public function encode($data)
	{
		return serialize($data);
	}

	/**
	 * serialize
	 *
	 * @param mixed $data
	 *
	 * @return  string
	 */
	public function decode($data)
	{
		return unserialize($data);
	}
}

