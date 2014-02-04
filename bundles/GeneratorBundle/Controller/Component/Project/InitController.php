<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Controller\Component\Project;


use GeneratorBundle\Action\Component\CopyBasefilesAction;
use GeneratorBundle\Controller\TaskController;
use LogicException;
use RuntimeException;

/**
 * Class InitController
 *
 * @since 1.0
 */
class InitController extends TaskController
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean  True if controller finished execution, false if the controller did not
	 *                   finish execution. A controller might return false if some precondition for
	 *                   the controller to run has not been satisfied.
	 *
	 * @throws  LogicException
	 * @throws  RuntimeException
	 */
	public function execute()
	{
		$this->doAction(new CopyBasefilesAction);
	}
}
