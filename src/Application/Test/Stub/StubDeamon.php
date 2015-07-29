<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Application\Test\Stub;

use Windwalker\Application\AbstractDaemonApplication;

/**
 * The StubDeamon class.
 * 
 * @since  2.0
 */
class StubDeamon extends AbstractDaemonApplication
{
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
		return;
	}
}
