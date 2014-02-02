<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Html;

use Joomla\DI\Container;
use Windwalker\Data\Data;
use Windwalker\Model\Model;
use Joomla\Registry\Registry;
use Windwalker\View\Helper\ToolbarHelper;

defined('JPATH_PLATFORM') or die;

/**
 * Prototype admin view.
 *
 * @package     Joomla.Libraries
 * @subpackage  Model
 * @since       3.2
 */
class HtmlView extends AbstractHtmlView
{
	/**
	 * @var  array  Property buttons.
	 */
	protected $buttons = array();

	/**
	 * @var  array  Property toolbarConfig.
	 */
	protected $toolbarConfig = array();

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
			$this->buttons = \JArrayHelper::getValue($config, 'buttons', null);
		}

		if (!$this->toolbarConfig)
		{
			$this->toolbarConfig = \JArrayHelper::getValue($config, 'toolbar', array());
		}
	}

	/**
	 * setTitle
	 *
	 * @param string $title
	 * @param string $icons
	 *
	 * @return  void
	 */
	protected function setTitle($title = null, $icons = 'stack')
	{
		$doc = $this->container->get('document');
		$doc->setTitle($title);

		\JToolbarHelper::title($title, $icons);
	}

	/**
	 * prepareRender
	 *
	 * @return  void
	 */
	protected function prepareRender()
	{
		parent::prepareRender();

		$data = $this->data;

		// View data
		$data->view = new Data;
		$data->view->prefix   = $this->prefix;
		$data->view->option   = $this->option;
		$data->view->name     = $this->getName();
		$data->view->viewItem = $this->viewItem;
		$data->view->viewList = $this->viewList;

		// Uri data
		$uri = \JUri::getInstance();
		$data->uri = new Data;
		$data->uri->path = $uri->toString(array('path', 'query', 'fragment'));
		$data->uri->base = \JUri::base(true);
		$data->uri->root = \JUri::root(true);

		// Asset data
		$data->asset = $this->container->get('helper.asset');
	}

	/**
	 * addToolbar
	 *
	 * @return  void
	 */
	protected function addToolbar()
	{
		$toolbar = $this->getToolbarHelper($this->toolbarConfig, $this->buttons);

		$toolbar->registerButtons();
	}

	/**
	 * getToolbarHelper
	 *
	 * @param array $config
	 * @param array $buttonSet
	 *
	 * @return  ToolbarHelper
	 */
	protected function getToolbarHelper($config = array(), $buttonSet = array())
	{
		$component = $this->container->get('component');
		$canDo     = $component->getActions($this->viewItem);
		$buttonSet = $buttonSet ? : $this->configureToolbar($this->buttons, $canDo);

		$defaultConfig = array(
			'view_name' => $this->getName(),
			'view_item' => $this->viewItem,
			'view_list' => $this->viewList,
			'option'    => $this->option,
			'access'    => $component->getActions($this->viewItem),
		);

		$config = with(new Registry($defaultConfig))
			->loadArray($config);

		return new ToolbarHelper($this->data, $buttonSet, $config);
	}

	/**
	 * configureToolbar
	 *
	 * @param array $buttonSet
	 * @param null  $canDo
	 *
	 * @return  array
	 */
	protected function configureToolbar($buttonSet = array(), $canDo = null)
	{
		return array();
	}
}
