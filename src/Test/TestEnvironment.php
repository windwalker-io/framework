<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Test;

/**
 * The TestEnvironment class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class TestEnvironment
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
