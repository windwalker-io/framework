<?php

use Windwalker\Data\Data;
use Windwalker\Data\NullData;
use Windwalker\View\Html\HtmlView;

/**
 * Class {{controller.list.name.cap}}HtmlView
 *
 * @since 1.0
 */
class {{extension.name.cap}}View{{controller.item.name.cap}}Html extends HtmlView
{
	public function render()
	{
		$data  = $this->getData();
		$model = $this->getModel();
		$input = $this->getContainer()->get('input');

		$model->getState()->set('{{controller.item.name.lower}}.id', $input->get('id'));

		$data->item = new Data($model->getItem()) ?: new NullData;
		$data->form = $model->getForm();

		$this->addToolbar();

		return parent::render();
	}

	protected function addToolbar()
	{
		$input = $this->getContainer()->get('input');

		$input->set('hidemainmenu', true);

		JToolbarHelper::title('{{controller.item.name.cap}} Edit');

		JToolbarHelper::apply('{{controller.item.name.lower}}.edit.apply');
		JToolbarHelper::save('{{controller.item.name.lower}}.edit.save');
		JToolbarHelper::save2new('{{controller.item.name.lower}}.edit.save2new');
		JToolbarHelper::save2copy('{{controller.item.name.lower}}.edit.save2copy');
		JToolbarHelper::cancel('{{controller.item.name.lower}}.edit.cancel');

//		JToolBarHelper::apply($this->item_name . '.apply');
//		JToolBarHelper::save($this->item_name . '.save');
//		JToolBarHelper::custom($this->item_name . '.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
//		JToolBarHelper::custom($this->item_name . '.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
//		JToolBarHelper::cancel($this->item_name . '.cancel');
	}
}
