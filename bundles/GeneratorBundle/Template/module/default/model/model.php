<?php
/**
 * @package        Asikart.Module
 * @subpackage     {{extension.element.lower}}
 * @copyright      Copyright (C) 2014 SMS Taiwan, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * The {{extension.name.cap}} model to get data.
 *
 * @since 1.0
 */
class Mod{{extension.name.cap}}Model extends \JModelDatabase
{
	/**
	 * Get item list.
	 *
	 * @return  mixed Item list.
	 */
	public function getItems()
	{
		// Prepare Joomla! API
		$app   = JFactory::getApplication();
		$input = $app->input;
		$user  = JFactory::getUser();
		$date  = JFactory::getDate('now', JFactory::getConfig()->get('offset'));
		$doc   = JFactory::getDocument();
		$uri   = JUri::getInstance();

		// Get sample data.
		return $this->getSampleData();
	}

	// The following is example methods, please delete if you don't want them.
	// --------------------------------------------------------------------------------------------

	/**
	 * Get sample data.
	 *
	 * @return  mixed select list array.
	 */
	protected function getSampleData()
	{
		$params = $this->state;

		// Init DB
		$db     = $this->db;
		$query  = $db->getQuery(true);

		// Get Joomla! API
		$app   = JFactory::getApplication();
		$user  = JFactory::getUser();
		$date  = JFactory::getDate('now', JFactory::getConfig()->get('offset'));

		// Get Params and prepare data.
		$catid = $params->get('catid', 1);
		$order = $params->get('orderby', 'item.created');
		$dir   = $params->get('order_dir', 'DESC');

		// Category

		// If Choose all category, select ROOT category.
		if (!in_array(1, $catid))
		{
			$query->where("item.catid " . new JDatabaseQueryElement('IN()', $catid));
		}

		// Published
		$query->where('item.published > 0');

		$nullDate = $db->Quote($db->getNullDate());
		$nowDate  = $db->Quote($date->toSql(true));

		$query->where('(item.publish_up = ' . $nullDate . ' OR item.publish_up <= ' . $nowDate . ')');
		$query->where('(item.publish_down = ' . $nullDate . ' OR item.publish_down >= ' . $nowDate . ')');

		// View Level
		$query->where('item.access ' . new JDatabaseQueryElement('IN()', $user->getAuthorisedViewLevels()));

		// Language
		if ($app->getLanguageFilter())
		{
			$lang_code = $db->quote(JFactory::getLanguage()->getTag());
			$query->where("item.language IN ({$lang_code}, '*')");
		}

		// Prepare Tables
		$table = array(
			'item' => '#__{{extension.name.lower}}_{{controller.list.name.lower}}',
			'cat'  => '#__categories'
		);

		try
		{
			$select = Mod{{extension.name.cap}}Helper::getSelectList($table);

			// Load Data
			$items = array();

			$query->select($select)
				->from('#__{{extension.name.lower}}_{{controller.list.name.lower}} AS item')
				->join('LEFT', '#__categories AS cat ON item.catid = cat.id')
				->order("{$order} {$dir}");

			$items = (array) $db->setQuery($query)->loadObjectList();

			foreach ($items as $key => &$item)
			{
				$item->link = JRoute::_("index.php?option=com_{{extension.name.lower}}&view={{controller.item.name.lower}}&id={$item->id}&alias={$item->alias}&catid={$item->catid}");
			}
		}
		catch (\RuntimeException $e)
		{
			$items = range(1, 5);

			foreach ($items as $key => &$item)
			{
				$item = new JData;

				$item->item_title   = '{{extension.name.cap}} data - ' . ($key + 1);
				$item->link         = '#';
				$item->item_created = $date->toSQL(true);
			}
		}

		return $items;
	}
}
