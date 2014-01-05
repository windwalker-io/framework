<?php
/**
 * @package     Joomla.Cms
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Html;

use Windwalker\Registry\Registry;
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
	 * getToolbarHelper
	 *
	 * @param array $config
	 *
	 * @return  ToolbarHelper
	 */
	protected function getToolbarHelper($config = array())
	{
		$component = $this->container->get('component');

		$defaultConfig = array(
			'view_name' => $this->name,
			'view_item' => $this->viewItem,
			'view_list' => $this->viewList,
			'access' => $component->getActions($this->viewItem),
			'buttons' => array(
				10  => 'addNew',
				20  => 'editList',
				30  => 'duplicate',
				40  => 'publish',
				50  => 'unpublish',
				60  => 'checkin',
				70  => 'deleteList',
				80  => 'trash',
				90  => 'batch',
				// 100 => 'preferences',
			)
		);

		$config = with(new Registry($defaultConfig))
			->loadArray($config);

		return new ToolbarHelper($this->data, $config);
	}
}
