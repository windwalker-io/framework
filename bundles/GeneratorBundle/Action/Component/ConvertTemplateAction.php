<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Action\Component;

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
	 * doExecute
	 *
	 * @return  mixed|void
	 */
	public function doExecute()
	{
		$config = $this->config;

		$convertOperator = $this->container->get('operator.convert');

		$replace = array_flip($this->replace);

		foreach ($replace as &$val)
		{
			$val = '{{' . $val . '}}';
		}

		// Flip src and dest because we want to convert template.
		$src  = $this->config['dir.src'];
		$dest = $this->config['dir.dest'];

		// Remove dir first
		Folder::delete($dest);

		$convertOperator->copy($src, $dest, $replace);
	}
}
