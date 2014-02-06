<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Controller\Component;

use GeneratorBundle\Action;

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
		$this->doAction(new Action\CopyAllAction);

		if ($this->config['client'] == 'administrator')
		{
			$this->doAction(new Action\Component\ImportSqlAction);

			$this->doAction(new Action\Component\CopyLanguageAction);
		}
	}
}
