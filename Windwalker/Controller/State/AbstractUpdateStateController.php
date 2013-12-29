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
	 * Property stateName.
	 *
	 * @var string
	 */
	protected $stateName = null;

	/**
	 * Property stateValue.
	 *
	 * @var mixed
	 */
	protected $stateValue = null;

	/**
	 * prepareExecute
	 *
	 * @throws \LogicException
	 * @return void
	 */
	protected function prepareExecute()
	{
		parent::prepareExecute();

		if (!$this->stateName)
		{
			throw new \LogicException('You have set state name in controller.');
		}
	}

	/**
	 * doExecute
	 *
	 * @return mixed
	 */
	public function doExecute()
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
			$this->app->enqueueMessage($e->getMessage(), 'error');

			$this->app->redirect(\JRoute::_($this->getRedirectItemUrl($this->recordId, $this->urlVar), false));

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
		else
		{
			$pks = $this->cid;

			foreach ($pks as $i => $pk)
			{
				$this->table->reset();

				if ($this->table->load($pk))
				{
					if (!$this->allowEditState($this->table->getProperties(true)))
					{
						// Prune items that you can't change.
						unset($pks[$i]);

						$this->app->enqueueMessage(\JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
					}
				}
			}

			// Check in the items.
			$this->app->enqueueMessage(\JText::plural('JLIB_CONTROLLER_N_ITEMS_PUBLISHED', $this->model->updateState($pks, $this->stateValue)));
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
