<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Application\Test\Stub;

use Windwalker\Application\AbstractCliApplication;

/**
 * The AtubApplication class.
 *
 * @since  2.0
 */
class StubCli extends AbstractCliApplication
{
	/**
	 * Property executed.
	 *
	 * @var string
	 */
	public $executed;

	/**
	 * Method to run the application routines.  Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of task directly.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	protected function doExecute()
	{
		return 'Hello World';
	}

	/**
	 * Method to close the application.
	 *
	 * @param   integer|string  $message  The exit code (optional; default is 0).
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function close($message = 0)
	{
		return $message;
	}
}
