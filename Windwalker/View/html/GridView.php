<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Html;


use Windwalker\View\Helper\GridHelper;

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
		}
	}

	protected function addToolbar()
	{
		$app          = \JFactory::getApplication();
		$canDo        = \JHelperContent::getActions($this->option);

		\JToolbarHelper::title(\JText::_($this->textPrefix . '_' . strtoupper($this->getName()) . '_TITLE'), 'stack article');

		// Get the toolbar object instance
		$bar = \JToolBar::getInstance('toolbar');

		if ($canDo->get('core.create'))
		{
			\JToolBarHelper::addNew($this->viewItem . '.add');
		}

		if ($canDo->get('core.edit'))
		{
			\JToolBarHelper::editList($this->viewItem . '.edit');
		}

		if ($canDo->get('core.create'))
		{
			\JToolBarHelper::custom($this->viewList . '.batch.copy', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
		}

		if ($canDo->get('core.edit.state'))
		{
			\JToolBarHelper::divider();
			\JToolBarHelper::publish($this->viewList . '.state.publish', 'JTOOLBAR_PUBLISH', true);
			\JToolBarHelper::unpublish($this->viewList . '.state.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			\JToolbarHelper::checkin($this->viewList . '.check.checkin');

			\JToolBarHelper::divider();
		}

		// if ((JArrayHelper::getValue($filter_state, 'a.published') == -2 && $canDo->get('core.delete')) || $this->get('no_trash') || AKDEBUG)
		{
			\JToolbarHelper::deleteList(\JText::_('LIB_WINDWALKER_TOOLBAR_CONFIRM_DELETE'), $this->viewList . '.state.delete');
		}
		// elseif ($canDo->get('core.edit.state'))
		{
			\JToolbarHelper::trash($this->viewList . '.state.trash');
		}

		// Add a batch modal button
		if ($canDo->get('core.edit'))
		{
			\AKToolbarHelper::modal('JTOOLBAR_BATCH', 'batchModal');
		}

		if ($canDo->get('core.admin') && $app->isAdmin())
		{
			\AKToolBarHelper::preferences($this->option);
		}
	}

	/**
	 * getGridHelper
	 *
	 * @return GridHelper
	 */
	public function getGridHelper()
	{
		$config = array(
			'option'    => $this->option,
			'name'      => $this->getName(),
			'view_item' => $this->viewItem,
			'view_list' => $this->viewList,
			'orderCol'  => 'sakura.catid, sakura.ordering',
			'field'     => array(
				'ordering'    => 'ordering'
			)
		);

		return new GridHelper($this->data, $config);
	}
}
