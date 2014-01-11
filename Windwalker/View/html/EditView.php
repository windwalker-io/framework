<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Html;

use Joomla\DI\Container;
use Windwalker\Model\Model;

/**
 * Class GridHtmlView
 *
 * @since 1.0
 */
class EditView extends ItemHtmlView
{
	/**
	 * Method to instantiate the view.
	 *
	 * @param Model             $model     The model object.
	 * @param Container         $container DI Container.
	 * @param array             $config    View config.
	 * @param \SplPriorityQueue $paths     Paths queue.
	 */
	public function __construct(Model $model = null, Container $container = null, $config = array(), \SplPriorityQueue $paths = null)
	{
		parent::__construct($model, $container, $config, $paths);

		if (!$this->buttons)
		{
			$component = $this->container->get('component');
			$canDo     = $component->getActions($this->viewItem);

			$this->buttons = \JArrayHelper::getValue($config, 'buttons', $this->configureToolbar(null, $canDo));
		}

		if (!$this->toolbarConfig)
		{
			$this->toolbarConfig = \JArrayHelper::getValue($config, 'toolbar', array());
		}
	}

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

		if ($errors = $data->state->get('errors'))
		{
			$this->flash($errors);
		}

		// Configure UI
		$this->addToolbar();
		$this->setTitle();

		$input = $this->getContainer()->get('input');

		$input->set('hidemainmenu', true);
	}

	/**
	 * setTitle
	 *
	 * @param null   $title
	 * @param string $icons
	 *
	 * @return  void
	 */
	protected function setTitle($title = null, $icons = 'pencil-2')
	{
		if (!$title)
		{
			$name = \JText::_($this->textPrefix . '_VIEW_' . strtoupper($this->getName()));

			$title = \JText::sprintf('LIB_WINDWALKER_TITLE_ITEM_EDIT', $name);
		}

		parent::setTitle($title, 'pencil-2 article');
	}

	/**
	 * configureToolbar
	 *
	 * @param array  $buttonSet
	 * @param Object $canDo
	 *
	 * @return  array
	 */
	protected function configureToolbar($buttonSet = array(), $canDo = null)
	{
		return array(
			'apply' => array(
				'handler'  => 'apply',
				'args'     => array($this->viewItem . '.edit.apply'),
				'access'   => true,
				'priority' => 1000
			),

			'save' => array(
				'handler'  => 'save',
				'args'     => array($this->viewItem . '.edit.save'),
				'access'   => true,
				'priority' => 900
			),

			'save2new' => array(
				'handler'  => 'save2new',
				'args'     => array($this->viewItem . '.edit.save2new'),
				'access'   => true,
				'priority' => 800
			),

			'save2copy' => array(
				'handler'  => 'save2copy',
				'args'     => array($this->viewItem . '.edit.save2copy'),
				'access'   => true,
				'priority' => 700
			),

			'cancel' => array(
				'handler'  => 'cancel',
				'args'     => array($this->viewItem . '.edit.cancel'),
				'access'   => true,
				'priority' => 600
			),
		);
	}
}
