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
			$this->setMessage($e->getMessage(), 'error');

			$this->app->redirect(\JRoute::_($this->getRedirectListUrl(), false));

			return false;
		}

		return true;
	}

	/**
	 * doUpdate
	 *
	 * @return void
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

		// Check in the items.
		$this->setMessage(\JText::plural($this->option . '_N_ITEMS_PUBLISHED', $this->model->updateState($pks, $this->stateData)));
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
		$this->app->redirect(\JRoute::_($this->getRedirectListUrl(), false));

		return $return;
	}

	protected function preUpdateHook()
	{
	}

	protected function postUpdateHook($model)
	{
	}
}
