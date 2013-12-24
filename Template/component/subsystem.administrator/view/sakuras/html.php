<?php

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
	protected $list_name = '';

	/**
	 * Item name.
	 *
	 * @var string
	 */
	protected $item_name = '';

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
		echo 'View Sakuras';

		$model = $this->getModel();
		$data  = $this->getData();

		$data->items = $this->get('Items');
		$this->state = $model->getState();

		$this->addToolbar();

		return parent::render();
	}

	protected function addToolbar()
	{
		$app          = JFactory::getApplication();
		$canDo        = FlowerHelper::getActions($this->option);
		$user         = JFactory::getUser();

		$filter_state = $this->state->get('filter');

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
			JToolBarHelper::custom($this->list_name . '.duplicate', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::publish($this->list_name . '.publish', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::unpublish($this->list_name . '.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::checkin($this->list_name . '.checkin');

			if ($this->state->get('items.nested'))
			{
				JToolBarHelper::custom($this->list_name . '.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
			}

			JToolBarHelper::divider();
		}

		if ((JArrayHelper::getValue($filter_state, 'a.published') == -2 && $canDo->get('core.delete')) || $this->get('no_trash') || AKDEBUG)
		{
			JToolbarHelper::deleteList(JText::_('LIB_WINDWALKER_TOOLBAR_CONFIRM_DELETE'), $this->list_name . '.delete');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash($this->list_name . '.trash');
		}

		// Add a batch modal button
		$batch = AKHelper::_('path.get', null, $this->option) . '/views/' . $this->list_name . '/tmpl/default_batch.php';

		if ($canDo->get('core.edit') && JVERSION >= 3 && JFile::exists($batch))
		{
			AKToolbarHelper::modal('JTOOLBAR_BATCH', 'batchModal');
		}

		if ($canDo->get('core.admin') && $app->isAdmin())
		{
			AKToolBarHelper::preferences($this->option);
		}
	}
}
