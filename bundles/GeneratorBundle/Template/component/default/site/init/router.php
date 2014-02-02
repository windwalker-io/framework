<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_flower
 * @author      Simon ASika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * @param    array    A named array
 *
 * @return    array
 */
function FlowerBuildRoute(&$query)
{
	$segments = array();

	$params = array(
		'view',
		'layout',
		'id'
	);

	/*
	// REST URI begin
	// ================================================================================================
	// If you dont't want to use REST URI ,uncomment this.


	// get a menu item based on Itemid or currently active
	// ================================================================================================
	$app        = JFactory::getApplication();
	$menu       = $app->getMenu();

	// set Menu Itemid
	// ================================================================================================
	if (empty($query['Itemid'])) {
		$menuItem = $menu->getActive();
		$menuItemGiven = false;
	}
	else {
		$menuItem = $menu->getItem($query['Itemid']);
		$menuItemGiven = true;
	}


	$com_menu = $menu->getItems( array('component'), array('com_flower') );

	// query item menu
	// ================================================================================================
	$item_menu = null ;
	foreach( $com_menu as $menuitem ):
		if(!isset($menuitem->query['id']) || !isset($query['id']) ) continue ;

		if( $menuitem->query['view'] == 'sakura' && $menuitem->query['id'] == $query['id'] ) {
			$item_menu = $menuitem ;
			//$segments = explode( '/', $item_menu->route);

			unset($query['id']);
			unset($query['view']);
			unset($query['catid']) ;
			unset($query['alias']) ;
			$query['Itemid'] = $item_menu->id ;

			return $segments ;
		}
	endforeach;

	// query item in category menu
	// ================================================================================================
	$item_menu = null ;
	foreach( $com_menu as $menuitem ):
		if(!isset($menuitem->query['id']) || !isset($query['catid']) ) continue ;

		if( $menuitem->query['view'] == 'sakuras' && $menuitem->query['id'] == $query['catid'] ) {
			$item_menu = $menuitem ;
			//$segments = explode( '/', $item_menu->route);

			$last = $query['id'] ;
			$last .= isset($query['alias']) ? '-' . $query['alias'] : '' ;
			$segments[] = $last ;

			unset($query['id']);
			unset($query['view']);
			unset($query['catid']) ;
			unset($query['alias']) ;
			$query['Itemid'] = $item_menu->id ;

			return $segments ;
		}
	endforeach;



	// query category menu
	// ================================================================================================
	$cat_menu = null ;
	foreach( $com_menu as $menuitem ):
		if(!isset($menuitem->query['id']) || !isset($query['id']) ) continue ;

		if( $menuitem->query['view'] == 'sakuras' && $menuitem->query['id'] == $query['id'] ) {
			$cat_menu = $menuitem ;

			unset($query['view']);
			unset($query['id']);
			$query['Itemid'] = $cat_menu->id ;

			return $segments ;
		}
	endforeach;


	// ================================================================================================
	// REST URI end
	*/

	// No menu match, use rest uri
	// ================================================================================================
	foreach ($params as $param):
		if (isset($query[$param]))
		{
			$segments[] = $query[$param];
			unset($query[$param]);
		}
	endforeach;

	// Unset needless query
	// ================================================================================================
	unset($query['catid']);
	unset($query['alias']);

	return $segments;
}

/**
 * @param    array    A named array
 * @param    array
 *                    Formats:
 *                    index.php?/banners/task/id/Itemid
 *                    index.php?/banners/id/Itemid
 */
function FlowerParseRoute($segments)
{
	$vars = array();

	// view is always the first element of the array
	$count = count($segments);

	$params = array(
		'view',
		'layout',
		'id'
	);

	// API System
	// ================================================================================================
	// If need API support, uncomment this.
	/*
	if($segments[0] == 'api') {
		array_shift($segments);

		include_once JPATH_ADMINISTRATOR.'/components/com_flower/includes/api/api.init.php' ;

		$params = array(
			'class',
			'method',
			'id'
		);
	}
	*/

	/*
	// REST URI begin
	// ================================================================================================
	// If you dont't want to use REST URI ,uncomment this.

	$last_seg = array_pop($segments);

	if(!is_numeric($last_seg)){
		$last_seg = explode(':', $last_seg);
		$id = $last_seg[0];
		if(is_numeric($id)){
			$segments[] = $id ;
			$vars['view'] = 'sakura' ;

		}
	}else{
		$segments[] = $last_seg ;
	}

	// ================================================================================================
	// REST URI end
	*/

	foreach ($params as $param):
		if ($count)
		{
			$count--;
			$segment = array_shift($segments);
			if (is_numeric($segment))
			{
				$vars['id'] = $segment;
			}
			elseif ($segment)
			{
				$vars[$param] = $segment;
			}
		}
	endforeach;

	/*
	if ($count)
	{
		$count--;
		$segment = array_shift($segments) ;
		if (is_numeric($segment)) {
			$vars['id'] = $segment;
		}
	}
	*/

	return $vars;
}
