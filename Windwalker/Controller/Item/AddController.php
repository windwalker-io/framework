<?php

namespace Windwalker\Controller\Item;

/**
 * Class AddController
 *
 * @since 1.0
 */
class AddController extends AbstractFormController
{
	/**
	 * execute
	 *
	 * @return $this|bool
	 */
	public function execute()
	{
		$context = "$this->option.edit.$this->context";

		// Access check.
		if (!$this->allowAdd())
		{
			// Set the internal error and also the redirect error.
			$this->app->enqueueMessage(\JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'), 'error');

			$this->redirect(
				\JRoute::_(
					'index.php?option=' . $this->option . '&view=' . strtolower($this->getName()) . $this->getRedirectToListAppend(),
					false
				)
			);

			return false;
		}

		// Clear the record edit information from the session.
		$this->app->setUserState($context . '.data', null);

		// Redirect to the edit screen.
		$this->redirect(
			\JRoute::_(
				'index.php?option=' . $this->option . '&view=' . strtolower($this->getName()) . $this->getRedirectToListAppend(),
				false
			)
		);

		return true;
	}
}
