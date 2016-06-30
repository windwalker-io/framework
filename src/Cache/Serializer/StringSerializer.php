<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Cache\Serializer;

/**
 * Class RawHandler
 *
 * @since 2.0
 */
class StringSerializer implements SerializerInterface
{
	/**
	 * Encode data.
	 *
	 * @param   mixed  $data
	 *
	 * @throws \InvalidArgumentException
	 * @return  string
	 */
	public function serialize($data)
	{
		if (is_array($data) || (is_object($data) && !method_exists($data, '_toString')))
		{
			throw new \InvalidArgumentException(__CLASS__ . ' can not handle an array or non-stringable object.');
		}

		return $data;
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
		return $data;
	}
}

