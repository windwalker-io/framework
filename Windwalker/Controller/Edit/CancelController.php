<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Windwalker\Controller\Edit;

use Windwalker\Controller\Admin\AbstractItemController;

defined('_JEXEC') or die;

/**
 * Class CancelController
 *
 * @since 1.0
 */
class CancelController extends AbstractItemController
{
	/**
	 * Generic method to cancel
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	protected function doExecute()
	{
		// Attempt to check-in the current record.
		if ($this->recordId && property_exists($this->table, 'checked_out'))
		{
			try
			{
				$this->model->checkin($this->recordId);
			}
			catch (\Exception $e)
			{
				$this->app->enqueueMessage(\JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $e->getMessage()));

				$this->redirectToItem($this->recordId, $this->urlVar);
			}
		}

		// Clean the session data and redirect.
		$this->releaseEditId($this->context, $this->recordId);
		$this->app->setUserState($this->context . '.data', null);

		$this->redirectToList();

		return true;

	}
}
