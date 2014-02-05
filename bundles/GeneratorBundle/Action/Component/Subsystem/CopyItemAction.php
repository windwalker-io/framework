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
class CopyItemAction extends AbstractAction
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

		$item = $this->config['item_name'];

		$files = array(
			'controller/%s',
			'model/form/%s.xml',
			'model/%s.php',
			'view/%s'
		);

		if ($this->config['client'] == 'administrator')
		{
			$files[] = 'table/%s.php';
		}

		foreach ($files as $file)
		{
			$file = sprintf($file, $item);

			$copyOperator->copy(
				$src . '/' . $file,
				$dest . '/' . $file,
				$this->config['replace']
			);
		}
	}
}
