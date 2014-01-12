<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use Windwalker\Data\Data;
use Windwalker\View\Html\ListHtmlView;

/**
 * Class FlowerViewSakurasHtml
 *
 * @since 1.0
 */
class FlowerViewSakurasHtml extends ListHtmlView
{
	/**
	 * prepareData
	 *
	 * @return  void
	 */
	protected function prepareData()
	{
		parent::prepareData();

		$app  = $this->container->get('app');
		$data = $this->data;

		$data->category = $this->get('Category');
		$data->params   = $data->state->get('params');

		$items = $data->items;

		// Set Data
		// =====================================================================================
		foreach ($items as &$item)
		{
			$item         = new JObject($item);
			$item->params = $item->params = new JRegistry($item->params);

			// Link
			// =====================================================================================
			$item->link = new JURI("index.php?option=com_flower&view=sakura&id={$item->id}");
			$item->link->setVar('alias', $item->get('a_alias'));
			$item->link->setVar('catid', $item->get('a_catid'));
			$item->link = JRoute::_((string) $item->link);

			// Publish Date
			// =====================================================================================
			$pup  = JFactory::getDate($item->get('a_publish_up'), JFactory::getConfig()->get('offset'))->toUnix(true);
			$pdw  = JFactory::getDate($item->get('a_publish_down'), JFactory::getConfig()->get('offset'))->toUnix(true);
			$now  = JFactory::getDate('now', JFactory::getConfig()->get('offset'))->toUnix(true);
			$null = JFactory::getDate('0000-00-00 00:00:00', JFactory::getConfig()->get('offset'))->toUnix(true);

			if (($now < $pup && $pup != $null) || ($now > $pdw && $pdw != $null))
			{
				$item->published = 0;
			}

			if ($item->modified == '0000-00-00 00:00:00')
			{
				$item->modified = '';
			}

			// Plugins
			// =====================================================================================
			$item->event = new stdClass;

			$dispatcher = $this->container->get('event.dispatcher');
			$item->text = $item->introtext;
			$results = $dispatcher->trigger('onContentPrepare', array('com_flower.sakura', &$item, &$this->params, 0));

			$results = $dispatcher->trigger('onContentAfterTitle', array('com_flower.sakura', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_flower.sakura', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_flower.sakura', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
		}

		// Category Params
		// =====================================================================================
		$registry = new JRegistry;
		$registry->loadString($data->category->params);
		$data->category->params = $registry;

		// Set title
		// =====================================================================================
		$active = $app->getMenu()->getActive();

		if ($active)
		{
			$currentLink = $active->link;

			if (!strpos($currentLink, 'view=sakuras') || !(strpos($currentLink, 'id=' . (string) $data->category->id)))
			{
				// If not Active, set Title
				$this->setTitle($data->category->title);
			}
		}
		else
		{
			$this->setTitle($data->category->title);
		}

		// Count Leading, Items & Links Number
		// =====================================================================================
		$numLeading = $data->params->def('num_leading_articles', $data->state->get('list.num_leading'));
		$numIntro   = $data->params->def('num_intro_articles', $data->state->get('list.num_intro'));
		$numLinks   = $data->params->def('num_links', $data->state->get('list.num_links'));

		// For blog layouts, preprocess the breakdown of leading, intro and linked articles.
		// This makes it much easier for the designer to just interrogate the arrays.
		$max = count($data->items);

		// The first group is the leading articles.
		$limit = $numLeading;
		$data->lead_items = new ArrayObject;

		for ($i = 0; $i < $limit && $i < $max; $i++)
		{
			$data->lead_items[$i] = $items[$i];
		}

		// The second group is the intro articles.
		$limit = $numLeading + $numIntro;
		$data->intro_items = new ArrayObject;

		// Order articles across, then down (or single column mode)
		for ($i = $numLeading; $i < $limit && $i < $max; $i++)
		{
			$data->intro_items[$i] = $items[$i];
		}

		$data->columns = max(1, $data->params->def('num_columns', 2));
		$order         = $data->params->def('multi_column_order', 1);

		$limit = $numLeading + $numIntro + $numLinks;
		$data->link_items = new ArrayObject;

		// The remainder are the links.
		for ($i = $numLeading + $numIntro; $i < $limit && $i < $max; $i++)
		{
			$data->link_items[$i] = $items[$i];
		}

		$data->items = $items;
	}
}
