<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Action;

use GeneratorBundle\Controller\TaskController;

/**
 * Class Action
 *
 * @since 1.0
 */
abstract class Action
{
	/**
	 * execute
	 *
	 * @param TaskController $controller
	 *
	 * @return  void
	 */
	abstract public function execute(TaskController $controller);

	public function copy($src, $dest)
	{

	}
}
