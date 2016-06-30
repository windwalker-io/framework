<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Cache\Serializer;

/**
 * Interface DataHandlerInterface
 */
interface SerializerInterface
{
	/**
	 * Encode data.
	 *
	 * @param   mixed  $data
	 *
	 * @return  string
	 */
	public function serialize($data);

	/**
	 * Decode data.
	 *
	 * @param   string  $data
	 *
	 * @return  mixed
	 */
	public function unserialize($data);
}
