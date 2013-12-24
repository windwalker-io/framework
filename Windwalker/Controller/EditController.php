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
	 * execute
	 *
	 * @return $this|bool
	 */
	public function execute()
	{
		$app   = \JFactory::getApplication();
		$model = $this->getModel();
		$table = $model->getTable();
		$cid   = $this->input->post->get('cid', array(), 'array');
		$context = "$this->option.edit.$this->context";

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		// Get the previous record id (if any) and the current record id.
		$recordId = (int) (count($cid) ? $cid[0] : $this->input->getInt($urlVar));

		// Access check.
		if (!$this->allowEdit(array($key => $recordId), $key))
		{
			// Set the internal error and also the redirect error.
			$this->app->enqueueMessage(\JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');

			$this->redirect(\JRoute::_($this->getRedirectListUrl(), false));

			return false;
		}

		/*
		// Attempt to check-out the new record for editing and redirect.
		$checkin = property_exists($table, 'checked_out');

		if ($checkin && !$model->checkout($recordId))
		{
			// Check-out failed, display a notice but allow the user to see the record.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKOUT_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				 \JRoute::_(
					   'index.php?option=' . $this->option . '&view=' . $this->view_item
					   . $this->getRedirectToItemAppend($recordId, $urlVar), false
				 )
			);

			return false;
		}
		*/

		// Check-out succeeded, push the new record id into the session.
		$this->holdEditId($context, $recordId);
		$app->setUserState($context . '.data', null);

		$this->input->set('layout', 'edit');

		$this->redirect(\JRoute::_($this->getRedirectItemUrl($recordId, $urlVar), false));
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		return $this->user->authorise('core.edit', $this->option);
	}
}
