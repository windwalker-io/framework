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

// Install WindWalker
// ========================================================================
$installer    = new JInstaller;
$install_path = $path . '/windwalker';

// Version compare
$windwalker_xml_path = JPATH_LIBRARIES . '/windwalker/windwalker.xml';
$install_windwalker  = true;

if (JFile::exists($windwalker_xml_path))
{
	$class = 'SimpleXMLElement';

	if (class_exists('JXMLElement'))
	{
		$class = 'JXMLElement';
	}

	$windwalker_xml = simplexml_load_file($windwalker_xml_path, $class);
	$install_xml    = simplexml_load_file($install_path . '/windwalker.xml', $class);

	if ((string) $install_xml->version <= (string) $windwalker_xml->version)
	{
		$install_windwalker = false;
	}
}

// Do install
if ($install_windwalker)
{
	if ($result[] = $installer->install($install_path))
	{
		$status = $tick;
	}
	else
	{
		$status = $cross;
	}

	// Set success table
	$grid->addRow(array('class' => 'row' . ($i % 2)));
	$grid->setRowCell('num', ++$i, $td_class);
	$grid->setRowCell('type', JText::_('COM_INSTALLER_TYPE_LIBRARY'), $td_class);
	$grid->setRowCell('name', JText::_('LIB_WINDWALKER'), array());
	$grid->setRowCell('version', $installer->manifest->version, $td_class);
	$grid->setRowCell('state', $status, $td_class);
	$grid->setRowCell('info', JText::_($installer->manifest->description), array());
}
