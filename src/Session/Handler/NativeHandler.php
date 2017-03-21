<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Session\Handler;

/**
 * Class PhpHandler
 *
 * @since 2.0
 */
class NativeHandler extends \SessionHandler implements HandlerInterface
{
	/**
	 * isSupported
	 *
	 * @return  boolean
	 */
	public static function isSupported()
	{
		return true;
	}

	/**
	 * register
	 *
	 * @return  mixed
	 */
	public function register()
	{
		return true;
	}
}

