<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router\Exception;

use Exception;

/**
 * The RouteNotFoundException class.
 * 
 * @since  2.0
 */
class RouteNotFoundException extends \RuntimeException
{
	/**
	 * RouteNotFoundException constructor.
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct($message = '', $code = 404, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
