<?php

namespace Windwalker\Controller\Edit;

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
	protected function doExecute()
	{
		$cid = $this->input->post->get('cid', array(), 'array');

		// Get the previous record id (if any) and the current record id.
		$recordId = (int) (count($cid) ? $cid[0] : $this->recordId);

		// Access check.
		if (!$this->allowEdit(array($this->key => $recordId), $this->key))
		{
			// Set the internal error and also the redirect error.
			$this->setMessage(\JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');

			$this->redirect(\JRoute::_($this->getRedirectListUrl(), false));

			return false;
		}

		// Attempt to check-out the new record for editing and redirect.
		$this->fetch($this->prefix, strtolower($this->viewList) . '.check.checkout', array('cid' => array($recordId)));

		// Check-out succeeded, push the new record id into the session.
		$this->holdEditId($this->context, $recordId);

		$this->app->setUserState($this->context . '.data', null);

		$this->input->set('layout', 'edit');

		$this->redirectToItem($recordId, $this->urlVar);
	}
}
