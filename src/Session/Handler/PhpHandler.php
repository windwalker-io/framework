<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Session\Handler;

/**
 * Class PhpHandler
 *
 * @since 1.0
 */
class PhpHandler extends \SessionHandler implements HandlerInterface
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

