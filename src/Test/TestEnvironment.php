<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Test;

use Windwalker\Environment\PhpHelper;
use Windwalker\Environment\ServerHelper;

/**
 * The TestEnvironment class.
 * 
 * @since  2.0
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
