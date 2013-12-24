<?php

namespace Windwalker\Object;

/**
 * Class Object
 *
 * @since 1.0
 */
class Object extends \JObject implements NullObjectInterface
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
