<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Controller\Component\Add;

use GeneratorBundle\Action\Component\Subsystem;
use GeneratorBundle\Controller\Component\ComponentController;

/**
 * Class SubsystemController
 *
 * @since 1.0
 */
class ItemController extends ComponentController
{
	/**
	 * Do Execute.
	 *
	 * @return  void
	 */
	protected function doExecute()
	{
		$this->config['item_name'] = '{{controller.item.name.lower}}';
		$this->config['list_name'] = '{{controller.list.name.lower}}';

		$this->doAction(new Subsystem\PrepareAction);

		$this->doAction(new Subsystem\CopyItemAction);
	}
}
