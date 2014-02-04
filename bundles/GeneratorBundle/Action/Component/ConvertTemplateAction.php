<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Action\Component;

use CodeGenerator\Controller\TaskController;
use GeneratorBundle\Action\AbstractAction;
use Joomla\Filesystem\Folder;

/**
 * Class ConvertTemplateAction
 *
 * @since 1.0
 */
class ConvertTemplateAction extends AbstractAction
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
		$config = $controller->config;

		$convertOperator = $this->container->get('operator.convert');

		$replace = array_flip($replace);

		foreach ($replace as &$val)
		{
			$val = '{{' . $val . '}}';
		}

		// Flip src and dest because we want to convert template.
		$src  = $config->get('dir.dest');
		$dest = $config->get('dir.src');

		// Remove dir first
		Folder::delete($dest);

		$convertOperator->copy($src, $dest, $replace);
	}
}
