<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Action\Component\Subsystem;

use GeneratorBundle\Action\AbstractAction;

/**
 * Class PrepareAction
 *
 * @since 1.0
 */
class CopyListAction extends AbstractAction
{
	/**
	 * doExecute
	 *
	 * @return  mixed
	 */
	protected function doExecute()
	{
		$copyOperator = $this->container->get('operator.copy');

		$src  = $this->config['dir.src'];
		$dest = $this->config['dir.dest'];

		$list = $this->config['list_name'];

		$files = array(
			'controller/%s',
			'model/form/%s',
			'model/%s.php',
			'view/%s'
		);

		foreach ($files as $file)
		{
			$file = sprintf($file, $list);

			$copyOperator->copy(
				$src . '/' . $file,
				$dest . '/' . $file,
				$this->config['replace']
			);
		}
	}
}
