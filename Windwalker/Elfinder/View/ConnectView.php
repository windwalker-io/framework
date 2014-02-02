<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Elfinder\View;

use elFinder;
use elFinderConnector;
use JPath;
use JURI;
use Joomla\Registry\Registry;
use Windwalker\View\Json\AbstractJsonView;

/**
 * Class ConnectView
 *
 * @since 1.0
 */
class ConnectView extends AbstractJsonView
{
	/**
	 * Property config.
	 *
	 * @var array
	 */
	protected $config = array();
	
	/**
	 * render
	 *
	 * @return string|void
	 */
	public function doRender()
	{
		// Init some API objects
		// ================================================================================
		$container  = $this->getContainer();
		$input      = $container->get('input');
		$config     = new Registry($this->config);

		// Set E_ALL for debuging
		error_reporting($config->get('error_reporting', 0));

		$elfinder_path = WINDWALKER . '/assets/js/elfinder/php/';

		include_once $elfinder_path . 'elFinderConnector.class.php';
		include_once $elfinder_path . 'elFinder.class.php';
		include_once $elfinder_path . 'elFinderVolumeDriver.class.php';

		/**
		 * Simple function to demonstrate how to control file access using "accessControl" callback.
		 * This method will disable accessing files/folders starting from '.' (dot)
		 *
		 * @param  string $attr attribute name (read|write|locked|hidden)
		 * @param  string $path file path relative to volume root directory started with directory separator
		 *
		 * @return bool|null
		 */
		function access($attr, $path)
		{
			// If file/folder begins with '.' (dot). Set read+write to false, other (locked+hidden) set to true
			if (strpos(basename($path), '.') === 0)
			{
				return !($attr == 'read' || $attr == 'write');
			}
			// Else elFinder decide it itself
			else
			{
				return null;
			}
		}

		// Get Some Request
		$root       = $input->get('root', '/');
		$start_path = $input->get('start_path', '/');

		$opts = array(
			// 'debug' => true,
			'roots' => array(
				array(
					// Driver for accessing file system (REQUIRED)
					'driver'        => 'LocalFileSystem',

					// Path to files (REQUIRED)
					'path'          => JPath::clean(JPATH_ROOT . '/' . $root, '/'),
					'startPath'     => JPath::clean(JPATH_ROOT . '/' . $root . '/' . $start_path . '/'),
					'URL'           => JPath::clean(JURI::root(true) . '/' . $root . '/' . $start_path, '/'), // URL to files (REQUIRED)
					'tmbPath'       => JPath::clean(JPATH_ROOT . '/cache/windwalker-finder-thumb'),
					'tmbURL'        => JURI::root(true) . '/cache/windwalker-finder-thumb',
					// 'tmbSize'       => 128,
					'tmp'           => JPath::clean(JPATH_ROOT . '/cache/windwalker-finder-temp'),

					// Disable and hide dot starting files (OPTIONAL)
					'accessControl' => 'access',
					'uploadDeny'    => array('text/x-php'),
					// 'uploadAllow'   => array('image'),
					'disabled'      => array('archive', 'extract', 'rename', 'mkfile')
				)
			)
		);

		$opts = array_merge($opts, $config->toArray());

		foreach ($opts['roots'] as $driver)
		{
			include_once $elfinder_path . 'elFinderVolume' . $driver['driver'] . '.class.php';
		}

		// Run elFinder
		$connector = new elFinderConnector(new elFinder($opts));
		$connector->run();

		exit();
	}

	/**
	 * getConfig
	 *
	 * @return  array
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * setConfig
	 *
	 * @param   array  $config
	 *
	 * @return  $this
	 */
	public function setConfig($config)
	{
		$this->config = $config;

		return $this;
	}
}
