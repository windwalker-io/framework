<?php

namespace Windwalker\Controller;

use Windwalker\Controller\Admin\AbstractItemController;

/**
 * Class AddController
 *
 * @since 1.0
 */
class EditController extends AbstractItemController
{
	/**
	 * doExecute
	 *
	 * @return mixed
	 */
	public function doExecute()
	{
		$cid = $this->input->post->get('cid', array(), 'array');

		// Get the previous record id (if any) and the current record id.
		$recordId = (int) (count($cid) ? $cid[0] : $this->recordId);

		// Access check.
		if (!$this->allowEdit(array($this->key => $recordId), $this->key))
		{
			// Set the internal error and also the redirect error.
			$this->app->enqueueMessage(\JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');

			$this->redirect(\JRoute::_($this->getRedirectListUrl(), false));

			return false;
		}

		// Attempt to check-out the new record for editing and redirect.
		$checkin = property_exists($this->table, 'checked_out');

		if ($checkin)
		{
			try
			{
				$this->model->checkout($recordId);
			}
			catch (\Exception $e)
			{
				// Check-out failed, display a notice but allow the user to see the record.
				$this->app->enqueueMessage(\JText::sprintf('JLIB_APPLICATION_ERROR_CHECKOUT_FAILED', $e->getMessage()), 'error');

				$this->redirectToList();
			}
		}

		// Check-out succeeded, push the new record id into the session.
		$this->holdEditId($this->context, $recordId);
		$this->app->setUserState($this->context . '.data', null);

		$this->input->set('layout', 'edit');

		$this->redirect(\JRoute::_($this->getRedirectItemUrl($recordId, $this->urlVar), false));
	}
}
