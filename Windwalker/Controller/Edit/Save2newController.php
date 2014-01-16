<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\Edit;

use Windwalker\Controller\Admin\AbstractItemController;
use Windwalker\Model\Exception\ValidateFailException;

/**
 * Class SaveController
 *
 * @since 1.0
 */
class Save2newController extends SaveController
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
		// Clear the record id and data from the session.
		$this->releaseEditId($this->context, $this->recordId);
		$this->app->setUserState($this->context . '.data', null);

		// Redirect back to the edit screen.
		$this->input->set('layout', 'edit');

		$this->redirectToItem();

		return $return;
	}
}
