<?php

use Windwalker\Data\Data;
use Windwalker\Data\NullData;
use Windwalker\View\Html\HtmlView;

/**
 * Class SakurasHtmlView
 *
 * @since 1.0
 */
class FlowerViewSakurasHtml extends HtmlView
{
	/**
	 * Items cache.
	 *
	 * @var array
	 */
	protected $items = null;

	/**
	 * Pagination cache.
	 *
	 * @var object
	 */
	protected $pagination = null;

	/**
	 * Model state.
	 *
	 * @var JRegistry
	 */
	protected $state = null;

	/**
	 * Component option name.
	 *
	 * @var string
	 */
	protected $option = '';

	/**
	 * List name.
	 *
	 * @var string
	 */
	protected $list_name = 'sakuras';

	/**
	 * Item name.
	 *
	 * @var string
	 */
	protected $item_name = 'sakura';

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var     string
	 */
	protected $textPrefix = 'COM_FLOWER';

	/**
	 * render
	 *
	 * @return string
	 */
	public function render()
	{
		$data                = $this->getData();
		$data->items         = $this->get('Items');
		$data->pagination    = $this->get('Pagination');
		$data->state         = $this->get('State');
		$data->filterForm    = $this->get('FilterForm');
		$data->batchForm     = $this->get('BatchForm');

		if ($errors = $data->state->get('errors'))
		{
			$this->flash($errors);
		}

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$data->sidebar = JHtmlSidebar::render();
		}

		return parent::render();
	}

	protected function addToolbar()
	{
		FlowerHelper::addSubmenu($this->getName());

		$app          = JFactory::getApplication();
		$canDo        = FlowerHelper::getActions($this->option);
		$user         = JFactory::getUser();

		$filter_state = (array) $this->data->state->get('filter');

		JToolbarHelper::title(JText::_($this->textPrefix . '_' . strtoupper($this->getName()) . '_TITLE'), 'stack article');

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		if ($canDo->get('core.create'))
		{
			JToolBarHelper::addNew($this->item_name . '.add');
		}

		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::editList($this->item_name . '.edit');
		}

		if ($canDo->get('core.create'))
		{
			JToolBarHelper::custom($this->list_name . '.batch.copy', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::publish($this->list_name . '.state.publish', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::unpublish($this->list_name . '.state.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::checkin($this->list_name . '.check.checkin');

			JToolBarHelper::divider();
		}

		// if ((JArrayHelper::getValue($filter_state, 'a.published') == -2 && $canDo->get('core.delete')) || $this->get('no_trash') || AKDEBUG)
		{
			JToolbarHelper::deleteList(JText::_('LIB_WINDWALKER_TOOLBAR_CONFIRM_DELETE'), $this->list_name . '.state.delete');
		}
		// elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash($this->list_name . '.state.trash');
		}

		// Add a batch modal button
		$batch = AKHelper::_('path.get', null, $this->option) . '/views/' . $this->list_name . '/tmpl/default_batch.php';

		if ($canDo->get('core.edit'))
		{
			AKToolbarHelper::modal('JTOOLBAR_BATCH', 'batchModal');
		}

		if ($canDo->get('core.admin') && $app->isAdmin())
		{
			AKToolBarHelper::preferences($this->option);
		}
	}
}
