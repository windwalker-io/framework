<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\IO\Filter;

/**
 * The NullFilter class.
 * 
 * @since  {DEPLOY_VERSION}
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
