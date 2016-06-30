<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Cache\Serializer;

/**
 * Class SerializeHandler
 *
 * @since 2.0
 */
class PhpSerializer implements SerializerInterface
{
	/**
	 * Encode data.
	 *
	 * @param   mixed  $data
	 *
	 * @return  string
	 */
	public function serialize($data)
	{
		return serialize($data);
	}

	/**
	 * Decode data.
	 *
	 * @param   string  $data
	 *
	 * @return  mixed
	 */
	public function unserialize($data)
	{
		return unserialize($data);
	}
}

