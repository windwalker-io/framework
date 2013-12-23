<?php

namespace Windwalker\Controller\Item;

/**
 * Class AddController
 *
 * @since 1.0
 */
class EditController extends AbstractFormController
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
		$checkin = property_exists($table, 'checked_out');

		// Access check.
		if (!$this->allowEdit(array($key => $recordId), $key))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				 \JRoute::_(
					   'index.php?option=' . $this->option . '&view=' . $this->view_list
					   . $this->getRedirectToListAppend(), false
				 )
			);

			return false;
		}

		// Attempt to check-out the new record for editing and redirect.
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
		else
		{
			// Check-out succeeded, push the new record id into the session.
			$this->holdEditId($context, $recordId);
			$app->setUserState($context . '.data', null);

			$this->setRedirect(
				 \JRoute::_(
					   'index.php?option=' . $this->option . '&view=' . $this->view_item
					   . $this->getRedirectToItemAppend($recordId, $urlVar), false
				 )
			);

			return true;
		}
	}
}
