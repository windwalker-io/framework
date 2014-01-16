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
class Save2copyController extends ApplyController
{
	/**
	 * preSaveHook
	 *
	 * @throws \Exception
	 * @return void
	 */
	protected function preSaveHook()
	{
		// Attempt to check-in the current record.
		$data = array('cid' => array($this->recordId), 'quiet' => true);

		$this->fetch($this->prefix, $this->viewList . '.check.checkin', $data);

		// Reset the ID and then treat the request as for Apply.
		$this->data[$this->key] = 0;
	}

	/**
	 * doExecute
	 *
	 * @return  mixed
	 */
	protected function doExecute()
	{
		$this->input->set('jform', $this->data);

		return $this->fetch($this->prefix, $this->name . '.edit.save', $this->input);
	}
}
