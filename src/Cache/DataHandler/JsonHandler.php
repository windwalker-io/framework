<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Cache\DataHandler;

/**
 * Class JsonHandler
 *
 * @since 2.0
 */
class JsonHandler implements DataHandlerInterface
{
	/**
	 * Encode data.
	 *
	 * @param   mixed  $data
	 *
	 * @return  string
	 */
	public function encode($data)
	{
		return json_encode($data);
	}

	/**
	 * Decode data.
	 *
	 * @param   string  $data
	 *
	 * @return  mixed
	 */
	public function decode($data)
	{
		return json_decode($data);
	}
}

