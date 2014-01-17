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
 * Class AddController
 *
 * @since 1.0
 */
class AddController extends AbstractItemController
{
	/**
	 * execute
	 *
	 * @return $this|bool
	 */
	protected function doExecute()
	{
		$context = "$this->option.edit.$this->context";

		// Access check.
		if (!$this->allowAdd())
		{
			// Set the internal error and also the redirect error.
			$this->setMessage(\JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'), 'error');

			$this->redirectToList();

			return false;
		}

		// Clear the record edit information from the session.
		$this->app->setUserState($context . '.data', null);

		// Redirect to the edit screen.
		$this->input->set('layout', 'edit');

		$this->redirectToItem();

		return true;
	}
}
