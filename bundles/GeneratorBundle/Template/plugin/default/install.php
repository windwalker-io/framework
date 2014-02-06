<?php
/**
 * @package        Asikart.Plugin
 * @subpackage     {{plugin.group.lower}}.plg_{{extension.name.lower}}
 * @copyright      Copyright (C) 2014 SMS Taiwan, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Script file of {{extension.name.cap}} Plugin.
 *
 * @since  1.0
 */
class Plg{{plugin.group.cap}}{{extension.name.cap}}InstallerScript
{
	/**
	 * Method to install the extension.
	 *
	 * @param \JInstallerAdapterPlugin $parent Parent installer adapter.
	 *
	 * @return  void
	 */
	public function install($parent)
	{
	}

	/**
	 * Method to uninstall the extension.
	 *
	 * @param \JInstallerAdapterPlugin $parent Parent installer adapter.
	 *
	 * @return  void
	 */
	public function uninstall($parent)
	{
	}

	/**
	 * Method to uninstall the extension.
	 *
	 * @param \JInstallerAdapterPlugin $parent Parent installer adapter.
	 *
	 * @return  void
	 */
	public function update($parent)
	{
	}

	/**
	 * Method to run before an install/update/uninstall method.
	 *
	 * @param string                   $type   Install type (install, update, discover_install, extension_site).
	 * @param \JInstallerAdapterPlugin $parent Parent installer adapter.
	 *
	 * @return  void
	 */
	public function preflight($type, $parent)
	{
	}

	/**
	 * Method to run after an install/update/uninstall method.
	 *
	 * @param string                   $type   Install type (install, update, discover_install, extension_site).
	 * @param \JInstallerAdapterPlugin $parent Parent installer adapter.
	 *
	 * @return  void
	 */
	public function postflight($type, $parent)
	{
	}
}