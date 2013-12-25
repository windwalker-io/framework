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
		$input = $this->getContainer()->get('input');

		$input->set('hidemainmenu', true);

		JToolbarHelper::title('Sakura Edit');

		JToolbarHelper::apply('sakura.save.apply');
		JToolbarHelper::save('sakura.save.save');
		JToolbarHelper::save2new('sakura.save.save2new');
		JToolbarHelper::save2copy('sakura.save.save2copy');
		JToolbarHelper::cancel('sakura.save.cancel');

//		JToolBarHelper::apply($this->item_name . '.apply');
//		JToolBarHelper::save($this->item_name . '.save');
//		JToolBarHelper::custom($this->item_name . '.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
//		JToolBarHelper::custom($this->item_name . '.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
//		JToolBarHelper::cancel($this->item_name . '.cancel');
	}
}
