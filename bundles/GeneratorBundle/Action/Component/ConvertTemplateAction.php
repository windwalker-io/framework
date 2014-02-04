<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Action\Component;

use GeneratorBundle\Action\Action;
use GeneratorBundle\Controller\TaskController;
use Windwalker\String\String;

/**
 * Class ConvertTemplateAction
 *
 * @since 1.0
 */
class ConvertTemplateAction extends Action
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
		show($replace);

		show($controller->config);

		$config = $controller->config;

		$copyOperator = $this->container->get('operator.copy');

		$replace = array_flip($replace);

		foreach ($replace as &$val)
		{
			$val = '{{' . $val . '}}';
		}

		// Flip src and dest because we want to convert template.
		$copyOperator->copy($config->get('dir.dest'), $config->get('dir.src'), $replace);
	}
}
