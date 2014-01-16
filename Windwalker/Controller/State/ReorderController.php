<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\State;

/**
 * Class ReorderController
 *
 * @since 1.0
 */
class ReorderController extends AbstractUpdateStateController
{
	/**
	 * Property stateData.
	 *
	 * @var string
	 */
	protected $stateData = array(
		'ordering' => '0'
	);

	/**
	 * Property actionText.
	 *
	 * @var string
	 */
	protected $actionText = 'UNPUBLISHED';

	/**
	 * Property ordering.
	 *
	 * @var int[]
	 */
	protected $ordering = array();

	/**
	 * Property reorderCondition.
	 *
	 * @var array
	 */
	protected $reorderConditions = array();

	/**
	 * prepareExecute
	 *
	 * @throws \LogicException
	 * @return void
	 */
	protected function prepareExecute()
	{
		parent::prepareExecute();

		$this->ordering = $this->input->get('order', array(), 'array');
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

		$pks      = $this->cid;
		$ordering = $this->ordering;

		foreach ($pks as $i => $pk)
		{
			$this->table->reset();

			if ($this->table->load($pk))
			{
				if (!$pk)
				{
					unset($pks[$i]);
					unset($ordering[$i]);

					continue;
				}

				if (!$this->allowUpdateState($this->table->getProperties(true)))
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					unset($ordering[$i]);

					$this->setMessage(\JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
				}
			}
		}

		// If reorder condition fields setted, we set them to state, or use model default.
		if (!empty($this->reorderConditions))
		{
			$this->model->getState()->set('reorder.condition.fields', $this->reorderConditions);
		}

		// Do reorder
		if (!$this->model->reorder($pks, $this->ordering))
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
	 * redirect
	 *
	 * @param string $url
	 * @param null   $msg
	 * @param string $type
	 *
	 * @return  void
	 */
	public function redirect($url, $msg = null, $type = 'message')
	{
		jexit($msg);
	}
}
