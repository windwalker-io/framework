<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Action\Component;

use GeneratorBundle\Action\AbstractAction;

/**
 * Class CopyItemControllerAction
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

		$config = $this->config;

		// $copyOperator->copy($config->get('dir.src') . '/' . $config->get(), $config->get('dir.dest'), $config->get('replace'));
	}
}
