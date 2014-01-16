<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\Edit;

use Windwalker\Controller\Admin\AbstractItemController;

/**
 * Class SaveController
 *
 * @since 1.0
 */
class ApplyController extends AbstractItemController
{
	/**
	 * doExecute
	 *
	 * @return  mixed
	 */
	protected function doExecute()
	{
		return $this->fetch($this->prefix, $this->name . '.edit.save');
	}

	/**
	 * postExecute
	 *
	 * @param null $return
	 *
	 * @return null
	 */
	protected function postExecute($return = null)
	{
		// Set the record data in the session.
		$this->recordId = $this->model->getState()->get($this->getName() . '.id');
		$this->holdEditId($this->context, $this->recordId);
		$this->app->setUserState($this->context . '.data', null);

		// Redirect back to the edit screen.
		$this->redirectToItem($this->recordId, $this->urlVar);

		return $return;
	}
}
