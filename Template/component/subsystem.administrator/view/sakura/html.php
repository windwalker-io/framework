<?php

use Windwalker\Data\Data;
use Windwalker\Data\NullData;
use Windwalker\View\Html\HtmlView;

/**
 * Class SakurasHtmlView
 *
 * @since 1.0
 */
class FlowerViewSakuraHtml extends HtmlView
{
	public function render()
	{
		$data  = $this->getData();
		$model = $this->getModel();
		$input = $this->getContainer()->get('input');

		$model->getState()->set('sakura.id', $input->get('id'));

		$data->item = new Data($model->getItem()) ?: new NullData;
		$data->form = $model->getForm();

		$this->addToolbar();

		return parent::render();
	}

	protected function addToolbar()
	{
		JToolbarHelper::title('Sakura Edit');

		JToolbarHelper::apply('sakura.apply');
		JToolbarHelper::save('sakura.save');
		JToolbarHelper::cancel('sakura.cancel');
	}
}
