<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Application\Test\Stub;

use Windwalker\Application\AbstractDaemonApplication;

/**
 * The StubDeamon class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class StubDeamon extends AbstractDaemonApplication
{
	/**
	 * Method to run the application routines.  Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of task directly.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	protected function doExecute()
	{
		return;
	}
}
