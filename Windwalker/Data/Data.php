<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

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
