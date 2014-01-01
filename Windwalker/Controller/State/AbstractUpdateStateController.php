<?php

namespace Windwalker\Controller\State;

use Windwalker\Controller\Admin\AbstractListController;

/**
 * Class AbstractUpdateStateController
 *
 * @since 1.0
 */
abstract class AbstractUpdateStateController extends AbstractListController
{
	/**
	 * Property stateData.
	 *
	 * @var string
	 */
	protected $stateData = array();

	/**
	 * Property actionText.
	 *
	 * @var string
	 */
	protected $actionText = 'STATE_CHANGED';

	/**
	 * prepareExecute
	 *
	 * @throws \LogicException
	 * @return void
	 */
	protected function prepareExecute()
	{
		parent::prepareExecute();

		if (!$this->stateData)
		{
			throw new \LogicException('You have to set state name in controller.');
		}
	}

	/**
	 * doExecute
	 *
	 * @return mixed
	 */
	protected function doExecute()
	{
		try
		{
			$this->preUpdateHook();

			$this->doUpdate();

			// Invoke the postSave method to allow for the child class to access the model.
			$this->postUpdateHook($this->model);

			// Set success message
		}
		// Other error here
		catch (\Exception $e)
		{
			if (JDEBUG)
			{
				echo $e;
			}

			$this->redirectToList($e->getMessage(), 'error');

			return false;
		}

		return true;
	}

	/**
	 * doUpdate
	 *
	 * @throws \InvalidArgumentException
	 * @return boolean
	 */
	public function doUpdate()
	{
		if (empty($this->cid))
		{
			throw new \InvalidArgumentException(\JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'), 500);
		}

		$pks = $this->cid;

		foreach ($pks as $i => $pk)
		{
			$this->table->reset();

			if ($this->table->load($pk))
			{
				if (!$pk)
				{
					unset($pks[$i]);

					continue;
				}

				if (!$this->allowUpdateState($this->table->getProperties(true)))
				{
					// Prune items that you can't change.
					unset($pks[$i]);

					$this->setMessage(\JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
				}
			}
		}

		if (!$this->model->updateState($pks, $this->stateData))
		{
			return false;
		}

		$errors = $this->model->getState()->get('error.message');

		if (count($errors))
		{
			$this->setMessage(implode('<br />', $errors));
		}
	}

	/**
	 * postExecute
	 *
	 * @param null $return
	 *
	 * @return mixed|null
	 */
	protected function postExecute($return = null)
	{
		// Check in the items.
		$msg = \JText::plural($this->option . '_N_ITEMS_' . $this->actionText, $this->model->getState()->get('success.number'));

		$this->redirectToList($msg);

		return $return;
	}

	/**
	 * preUpdateHook
	 *
	 * @return void
	 */
	protected function preUpdateHook()
	{
	}

	/**
	 * postUpdateHook
	 *
	 * @param $model
	 *
	 * @return void
	 */
	protected function postUpdateHook($model)
	{
	}
}
