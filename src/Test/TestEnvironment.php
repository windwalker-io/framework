<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Test;

use Windwalker\Environment\PhpHelper;
use Windwalker\Environment\ServerHelper;

/**
 * The TestEnvironment class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class TestEnvironment extends ServerHelper
{
	/**
	 * isCli
	 *
	 * @return  boolean
	 */
	public static function isCli()
	{
		return PhpHelper::isCli();
	}
}
