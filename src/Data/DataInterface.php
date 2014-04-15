<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Data;

/**
 * Interface DataInterface
 */
interface DataInterface
{
	/**
	 * bind
	 *
	 * @param      $values
	 * @param bool $replaceNulls
	 *
	 * @return  mixed
	 */
	public function bind($values, $replaceNulls = false);

	/**
	 * isNull
	 *
	 * @return  boolean
	 */
	public function isNull();
}
