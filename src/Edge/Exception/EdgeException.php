<?php
/**
 * Part of phoenix project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Edge\Exception;

use Exception;

/**
 * The EdgeException class.
 *
 * @since  {DEPLOY_VERSION}
 */
class EdgeException extends \Exception
{
	/**
	 * Construct the exception. Note: The message is NOT binary safe.
	 *
	 * @link  http://php.net/manual/en/exception.construct.php
	 *
	 * @param string    $message  [optional] The Exception message to throw.
	 * @param int       $code     [optional] The Exception code.
	 * @param Exception $previous [optional] The previous exception used for the exception chaining.
	 */
	public function __construct($message = null, $code = null, $file = null, $line = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);

		$this->file = $file;
		$this->line = $line;
	}
}
