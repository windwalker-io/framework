<?php
/**
 * @package     Joomla.Cms
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Html;

use Windwalker\Data\Data;
use Windwalker\Registry\Registry;
use Windwalker\View\Helper\ToolbarHelper;
use Windwalker\View\Toolbar\Button;

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
	 * setTitle
	 *
	 * @param string $title
	 * @param string $icons
	 *
	 * @return  void
	 */
	protected function setTitle($title = null, $icons = 'stack')
	{
		\JToolbarHelper::title($title, $icons);
	}

	protected function prepareRender()
	{
		parent::prepareRender();

		$data = $this->data;

		$data->view = new Data;
		$data->view->option   = $this->option;
		$data->view->name     = $this->getName();
		$data->view->viewItem = $this->viewItem;
		$data->view->viewList = $this->viewList;
	}

	/**
	 * getToolbarHelper
	 *
	 * @param array $config
	 *
	 * @return  ToolbarHelper
	 */
	protected function getToolbarHelper($config = array())
	{
		$component = $this->container->get('component');

		$buttonSet = $this->configToolbar();

		$defaultConfig = array(
			'view_name' => $this->getName(),
			'view_item' => $this->viewItem,
			'view_list' => $this->viewList,
			'option'    => $this->option,
			'access' => $component->getActions($this->viewItem),
		);

		$config = with(new Registry($defaultConfig))
			->loadArray($config);

		return new ToolbarHelper($this->data, $buttonSet, $config);
	}

	/**
	 * configToolbar
	 *
	 * @return  array
	 */
	protected function configToolbar()
	{
		return array();
	}
}
