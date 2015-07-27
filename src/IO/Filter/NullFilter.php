<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\IO\Filter;

/**
 * The NullFilter class.
 * 
 * @since  2.0
 */
class NullFilter
{
	/**
	 * clean
	 *
	 * @param string                 $source
	 * @param string|callable|object $filter
	 *
	 * @return  mixed
	 */
	public function clean($source, $filter = 'string')
	{
		return $source;
	}
}
