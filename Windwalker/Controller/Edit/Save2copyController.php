<?php

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
	 * @return void
	 */
	protected function preSaveHook()
	{
		// Check-in the original row.
		// if (property_exists($this->table, 'checked_out'))
		if (false)
		{
			try
			{
				$this->model->checkin($this->data[$this->key]);
			}
			catch (\Exception $e)
			{
				throw new \Exception(\JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $e->getMessage()));
			}
		}

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
