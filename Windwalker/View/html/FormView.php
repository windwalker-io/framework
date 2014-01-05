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
class FormView extends ItemHtmlView
{
	/**
	 * prepareRender
	 *
	 * @return  void
	 */
	protected function prepareRender()
	{
		parent::prepareRender();

		$data        = $this->getData();
		$data->form  = $this->get('Form');

		$this->addToolbar();

		if ($errors = $data->state->get('errors'))
		{
			$this->flash($errors);
		}
	}

	protected function addToolbar()
	{
		$input = $this->getContainer()->get('input');

		$input->set('hidemainmenu', true);

		\JToolbarHelper::title(ucfirst($this->viewItem) . ' Edit');

		\JToolbarHelper::apply($this->viewItem . '.edit.apply');
		\JToolbarHelper::save($this->viewItem . '.edit.save');
		\JToolbarHelper::save2new($this->viewItem . '.edit.save2new');
		\JToolbarHelper::save2copy($this->viewItem . '.edit.save2copy');
		\JToolbarHelper::cancel($this->viewItem . '.edit.cancel');
	}
}
