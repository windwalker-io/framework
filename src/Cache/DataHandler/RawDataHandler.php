<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Cache\DataHandler;

/**
 * The RawDataHandler class.
 * 
 * @since  2.1.2
 */
class RawDataHandler implements DataHandlerInterface
{
	/**
	 * Encode data.
	 *
	 * @param   mixed $data
	 *
	 * @return  string
	 */
	public function encode($data)
	{
		return $data;
	}

	/**
	 * Decode data.
	 *
	 * @param   string $data
	 *
	 * @return  mixed
	 */
	public function decode($data)
	{
		return $data;
	}
}
