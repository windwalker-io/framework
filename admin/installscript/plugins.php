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

// Install plugins
// ========================================================================
$plugins = $manifest->plugins;

if (!empty($plugins))
{
	foreach ($plugins as $plugin)
	{
		// Install per plugin
		foreach ($plugin as $var)
		{
			$var          = (string) $var;
			$install_path = $path . '/../plugins/' . $var;

			// Get plugin name
			$path2    = explode('/', $var);
			$plg_name = array_pop($path2);

			if (substr($plg_name, 0, 4) == 'plg_')
			{
				$plg_name = substr($plg_name, 4);
			}

			$plg_name = explode('_', $plg_name);
			$plg_name = $plg_name[1];

			// Do install
			$installer = new JInstaller;

			if ($result[] = $installer->install($install_path))
			{
				$plg_group = (string) $installer->manifest['group'];

				// Enable this plugin.
				if ($type == 'install')
				{
					$q = $db->getQuery(true);

					$q->update('#__extensions')
						->set("enabled = 1")
						->where("type = 'plugin'")
						->where("element = '{$plg_name}'")
						->where("folder = '{$plg_group}'");

					$db->setQuery($q);
					$db->execute();
				}

				$status = $tick;
			}
			else
			{
				$status = $cross;
			}

			// Set success table
			$grid->addRow(array('class' => 'row' . ($i % 2)));
			$grid->setRowCell('num', ++$i, $td_class);
			$grid->setRowCell('type', JText::_('COM_INSTALLER_TYPE_PLUGIN'), $td_class);
			$grid->setRowCell('name', JText::_($var), array());
			$grid->setRowCell('version', $installer->manifest->version, $td_class);
			$grid->setRowCell('state', $status, $td_class);
			$grid->setRowCell('info', JText::_($installer->manifest->description), array());
		}
	}
}

