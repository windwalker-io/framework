<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Install.Script
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

$db = JFactory::getDbo();

// Show Installed table
// ========================================================================
include_once $path . '/windwalker/html/grid.php';

$grid = new AKGrid;

$option['class'] = 'adminlist table table-striped table-bordered';
$option['style'] = JVERSION >= 3 ? 'width: 750px;' : 'width: 80%; margin: 15px;';
$grid->setTableOptions($option);
$grid->setColumns(array('num', 'type', 'name', 'version', 'state', 'info'));

$grid->addRow(array(), 1);
$grid->setRowCell('num', '#', array());
$grid->setRowCell('type', JText::_('COM_INSTALLER_HEADING_TYPE'), array());
$grid->setRowCell('name', JText::_('COM_INSTALLER_HEADING_NAME'), array());
$grid->setRowCell('version', JText::_('JVERSION'), array());
$grid->setRowCell('state', JText::_('JSTATUS'), array());
$grid->setRowCell('info', JText::_('COM_INSTALLER_MSG_DATABASE_INFO'), array());

// Set cells
$i = 0;

if (JVERSION >= 3)
{
	$tick  = '<i class="icon-publish"></i>';
	$cross = '<i class="icon-unpublish"></i>';
}
else
{
	$tick  = '<img src="templates/bluestork/images/admin/tick.png" alt="Success" />';
	$cross = '<img src="templates/bluestork/images/admin/publish_y.png" alt="Fail" />';
}

$td_class = array('style' => 'text-align:center;');

// Set Extension install success info
// ========================================================================
include dirname(__FILE__) . '/installscript/' . $manifest['type'] . '.php';

// Install WindWalker
// ========================================================================
include dirname(__FILE__) . '/installscript/windwalker.php';

// Install modules
// ========================================================================
include dirname(__FILE__) . '/installscript/modules.php';

// Install plugins
// ========================================================================
include dirname(__FILE__) . '/installscript/plugins.php';

// Render install information
// ========================================================================
if ($manifest['type'] == 'component')
{
	echo '<h1>' . JText::_(strtoupper($manifest->name)) . '</h1>';

	$img  = JURI::base() . '/components/' . strtolower($manifest->name) . '/images/' . strtolower($manifest->name) . '_logo.png';
	$img  = JHtml::_('image', $img, 'LOGO');
	$link = JRoute::_("index.php?option=" . $manifest->name);

	echo '<div id="ak-install-img">' . JHtml::link($link, $img) . '</div>';
	echo '<div id="ak-install-msg">' . JText::_(strtoupper($manifest->name) . '_INSTALL_MSG') . '</div>';
	echo '<br /><br />';
}

echo $grid;
