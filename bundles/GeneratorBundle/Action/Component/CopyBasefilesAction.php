<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Action\Component;

use GeneratorBundle\Action\AbstractAction;
use CodeGenerator\Controller\TaskController;

/**
 * Class CopyBasefilesAction
 *
 * @since 1.0
 */
class CopyBasefilesAction extends AbstractAction
{
	/**
	 * execute
	 *
	 * @param TaskController $controller
	 * @param array          $replace
	 *
	 * @return  void
	 */
	public function execute(TaskController $controller, $replace = array())
	{
		print_r($controller->config);

		// TODO: Implement execute() method.
	}
}
