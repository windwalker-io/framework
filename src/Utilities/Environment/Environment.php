<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Utilities\Environment;

/**
 * The Environment class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class Environment
{
	/**
	 * isWindows
	 *
	 * @return  boolean
	 */
	public static function isWindows()
	{
		return defined('PHP_WINDOWS_VERSION_BUILD');
	}
}
