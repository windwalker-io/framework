<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Profiler\Point;

/**
 * The CollectorInterface interface.
 * 
 * @since  {DEPLOY_VERSION}
 */
interface CollectorInterface
{
	/**
	 * Get a value.
	 *
	 * @param   string  $name     The data name you want to get.
	 * @param   mixed   $default  The default value if not exists.
	 *
	 * @return  mixed  The found value or default.
	 */
	public function get($name, $default = null);

	/**
	 * Get all data.
	 *
	 * @return  array
	 */
	public function getData();
}
