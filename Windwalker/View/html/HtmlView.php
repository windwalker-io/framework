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
	 * getToolbarHelper
	 *
	 * @param array $config
	 *
	 * @return  ToolbarHelper
	 */
	protected function getToolbarHelper($config = array())
	{
		$component = $this->container->get('component');
		$itemName = $this->viewItem;
		$listName = $this->viewList;

		$defaultConfig = array(
			'view_name' => $this->name,
			'view_item' => $this->viewItem,
			'view_list' => $this->viewList,
			'access' => $component->getActions($this->viewItem),
		);

		$buttonSet = array(
			'add'        => 'addNew',
			'edit'       => function() use($itemName)
				{
					\JToolBarHelper::editList($itemName . '.edit');
				},
			'duplicate'  => array(
				'code' => function() use($listName)
					{
						\JToolBarHelper::custom($listName . '.batch.copy', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
					},
				'access' => 'core.create'
			),
			'publish'    => 'publish',
			'ubpublish'  => 'unpublish',
			'checkin'    => 'checkin',
			'delete'     => 'deleteList',
			'trash'      => 'trash',
			'batch'      => 'batch',
			// 100 => 'preferences',
		);

		\AK::show(new \SplPriorityQueue($buttonSet));

		$config = with(new Registry($defaultConfig))
			->loadArray($config);

		return new ToolbarHelper($this->data, $buttonSet, $config);
	}
}
