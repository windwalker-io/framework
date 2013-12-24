<?php

namespace Windwalker\Data;

/**
 * Class Data
 *
 * @since 1.0
 */
class Data extends \JData implements NullDataInterface
{
	/**
	 * isNull
	 *
	 * @return boolean
	 */
	public function isNull()
	{
		return false;
	}
}
