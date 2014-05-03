<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Data;

/**
 * The Data Interface
 *
 * @since 2.0
 */
interface DataInterface
{
	/**
	 * Bind the data into this object.
	 *
	 * @param   mixed    $values       The data array or object.
	 * @param   boolean  $replaceNulls Replace null or not.
	 *
	 * @return  Data Return self to support chaining.
	 */
	public function bind($values, $replaceNulls = false);

	/**
	 * Is this object empty?
	 *
	 * @return  boolean
	 */
	public function isNull();
}
