<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
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

	/**
	 * Is this object has properties?
	 *
	 * @return  boolean
	 */
	public function notNull();

	/**
	 * Dump all data as array
	 *
	 * @return  array
	 */
	public function dump();
}
