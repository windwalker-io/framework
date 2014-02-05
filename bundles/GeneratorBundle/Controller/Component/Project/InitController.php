<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Controller\Component\Project;

use GeneratorBundle\Action\Component\CopyAllAction;
use GeneratorBundle\Action\Component\CopyLanguageAction;
use GeneratorBundle\Action\Component\ImportSqlAction;
use GeneratorBundle\Controller\Component\ComponentController;

/**
 * Class InitController
 *
 * @since 1.0
 */
class InitController extends ComponentController
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean  True if controller finished execution, false if the controller did not
	 *                   finish execution. A controller might return false if some precondition for
	 *                   the controller to run has not been satisfied.
	 *
	 * @throws  \LogicException
	 * @throws  \RuntimeException
	 */
	public function doExecute()
	{
		$this->doAction(new CopyAllAction);

		if ($this->config['client'] == 'administrator')
		{
			$this->doAction(new ImportSqlAction);

			$this->doAction(new CopyLanguageAction);
		}
	}
}
