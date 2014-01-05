<?php

use Windwalker\View\Html\GridView;

/**
 * Class SakurasHtmlView
 *
 * @since 1.0
 */
class FlowerViewSakurasHtml extends GridView
{
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
	 * render
	 *
	 * @return string
	 */
	protected function prepareData()
	{
	}

	protected function addToolbar()
	{
		FlowerHelper::addSubmenu($this->getName());

		parent::addToolbar();
	}
}
