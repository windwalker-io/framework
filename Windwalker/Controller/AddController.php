<?php

namespace Windwalker\Controller;

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
	public function doExecute()
	{
		$context = "$this->option.edit.$this->context";

		// Access check.
		if (!$this->allowAdd())
		{
			// Set the internal error and also the redirect error.
			$this->app->enqueueMessage(\JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'), 'error');

			$this->redirect(\JRoute::_($this->getRedirectListUrl(), false));

			return false;
		}

		// Clear the record edit information from the session.
		$this->app->setUserState($context . '.data', null);

		// Redirect to the edit screen.
		$this->input->set('layout', 'edit');

		$this->redirect(\JRoute::_($this->getRedirectItemUrl(), false));

		return true;
	}
}
