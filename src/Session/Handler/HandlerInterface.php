<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Session\Handler;

if (!interface_exists('SessionHandlerInterface'))
{
	include_once __DIR__ . '/../Compat/SessionHandlerInterface.php';
}

/**
 * Interface HandlerInterface
 */
interface HandlerInterface extends \SessionHandlerInterface
{
	/**
	 * isSupported
	 *
	 * @return  boolean
	 */
	public static function isSupported();

	/**
	 * register
	 *
	 * @return  mixed
	 */
	public function register();
}

