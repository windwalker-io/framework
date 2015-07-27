<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Application\Test\Stub;

use Windwalker\Application\AbstractWebApplication;

/**
 * The AtubApplication class.
 *
 * @since  2.0
 */
class StubWeb extends AbstractWebApplication
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
	 * @return  void
	 *
	 * @since   2.0
	 */
	protected function doExecute()
	{
		$this->setBody('Hello World');
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function execute()
	{
		$this->response->sentHeaders = array();

		// Perform application routines.
		$this->doExecute();

		// Send the application response.
		$this->respond();
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
