<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Html;


use JToolBarHelper;
use Windwalker\Helper\ArrayHelper;
use Windwalker\Registry\Registry;
use Windwalker\View\Helper\GridHelper;
use Windwalker\View\Helper\ToolbarHelper;

/**
 * Class GridHtmlView
 *
 * @since 1.0
 */
class GridView extends ListHtmlView
{
	/**
	 * prepareRender
	 *
	 * @return  void
	 */
	protected function prepareRender()
	{
		parent::prepareRender();

		$data             = $this->getData();
		$data->grid       = $this->getGridHelper();
		$data->filterForm = $this->get('FilterForm');
		$data->batchForm  = $this->get('BatchForm');

		if ($errors = $data->state->get('errors'))
		{
			$this->flash($errors);
		}

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$data->sidebar = \JHtmlSidebar::render();

			$this->setTitle();
		}
	}

	/**
	 * setTitle
	 *
	 * @param null   $title
	 * @param string $icons
	 *
	 * @return  void
	 */
	protected function setTitle($title = null, $icons = 'stack')
	{
		$title = $title ? : \JText::_($this->textPrefix . '_' . strtoupper($this->getName()) . '_TITLE');

		parent::setTitle($title, 'stack article');
	}

	/**
	 * Add the submenu.
	 *
	 * @return  void
	 *
	 * @since	3.2
	 */
	protected function addSubmenu()
	{
	}

	/**
	 * addToolbar
	 *
	 * @return  void
	 */
	protected function addToolbar()
	{
		$toolbar = $this->getToolbarHelper();

		$toolbar->registerButtons();
	}

	/**
	 * getGridHelper
	 *
	 * @return GridHelper
	 */
	public function getGridHelper($config = array())
	{
		$defaultConfig = array(
			'option'    => $this->option,
			'view_name'      => $this->getName(),
			'view_item' => $this->viewItem,
			'view_list' => $this->viewList,
			'orderCol'  => $this->viewItem . '.catid, ' . $this->viewItem . '.ordering'
		);

		$config = array_merge($defaultConfig, $config);

		return new GridHelper($this->data, $config);
	}

	/**
	 * configButtonSet
	 *
	 * @param array $buttonSet
	 *
	 * @return  array
	 */
	protected function configToolbar($buttonSet = array())
	{
		$component = $this->container->get('component');
		$canDo     = $component->getActions($this->viewItem);
		$state     = $this->data->state ? : new Registry;
		$grid      = $this->data->grid;

		$filterState = $state->get('filter');

		return array(
			'add' => array(
				'handler'  => 'addNew',
				'args'     => array($this->viewItem . '.edit.add'),
				'access'   => 'core.create',
				'priority' => 1000
			),

			'edit' => array(
				'handler'  => 'editList',
				'args'     => array($this->viewItem . '.edit.edit'),
				'access'   => 'core.edit',
				'priority' => 900
			),

			'duplicate' => array(
				'handler'  => 'duplicate',
				'args'     => array($this->viewList . '.batch.copy'),
				'access'   => 'core.create',
				'priority' => 800
			),

			'publish' => array(
				'handler'  => 'publish',
				'args'     => array($this->viewList . '.state.publish'),
				'access'   => 'core.edit.state',
				'priority' => 1000
			),

			'unpublish' => array(
				'handler'  => 'unpublish',
				'args'     => array($this->viewList . '.state.unpublish'),
				'access'   => 'core.create',
				'priority' => 700
			),

			'checkin' => array(
				'handler'  => 'checkin',
				'args'     => array($this->viewList . '.state.checkin'),
				'access'   => 'core.create',
				'priority' => 600
			),

			'delete' => array(
				'handler' => 'deleteList',
				'args'     => array($this->viewList . '.state.delete'),
				'access'  => (
					ArrayHelper::getValue($filterState, $grid->config->get('field.state', 'published'))
					&& $canDo->get('core.delete')
				),
				'priority' => 500
			),

			'trash' => array(
				'handler' => 'trash',
				'args'     => array($this->viewList . '.state.trash'),
				'access'  => (
					!ArrayHelper::getValue($filterState, $grid->config->get('field.state', 'published'))
					&& $canDo->get('core.edit.state')
				),
				'priority' => 400
			),

			'batch' => array(
				'handler'  => 'modal',
				'access'   => 'core.edit',
				'priority' => 300
			),

			'preferences' => array(
				'handler' => 'preferences',
				'access'   => 'core.edit',
				'priority' => 200
			),
		);
	}
}
