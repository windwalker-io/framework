<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Action\Component\Subsystem;

use GeneratorBundle\Action\AbstractAction;
use GeneratorBundle\Action\Component;

/**
 * Class PrepareAction
 *
 * @since 1.0
 */
class PrepareAction extends AbstractAction
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

		$copyOperator->copy(
			$src . '/model/field',
			$dest . '/model/field',
			$this->config['replace']
		);

		$this->controller->doAction(new Component\ImportSqlAction);

		$this->controller->doAction(new Component\CopyLanguageAction);
	}
}
