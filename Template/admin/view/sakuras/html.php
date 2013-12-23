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
	 * render
	 *
	 * @return string
	 */
	public function render()
	{
		echo 'View Sakuras';

		$model = $this->getModel();
		$data  = $this->getData();

		$data->items = $model->getItems();

		return parent::render();
	}
}
