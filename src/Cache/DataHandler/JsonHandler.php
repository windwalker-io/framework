<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Cache\DataHandler;

/**
 * Class JsonHandler
 *
 * @since {DEPLOY_VERSION}
 */
class JsonHandler implements DataHandlerInterface
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
		return json_encode($data);
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
		return json_decode($data);
	}
}

