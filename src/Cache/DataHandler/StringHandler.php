<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Cache\DataHandler;

/**
 * Class RawHandler
 *
 * @since 2.0
 */
class StringHandler implements DataHandlerInterface
{
	/**
	 * Encode data.
	 *
	 * @param   mixed  $data
	 *
	 * @throws \InvalidArgumentException
	 * @return  string
	 */
	public function encode($data)
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
	public function decode($data)
	{
		return $data;
	}
}

