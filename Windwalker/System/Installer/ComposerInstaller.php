<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\System\Installer;

use Composer\Script\CommandEvent;

/**
 * Class ComposerInstaller
 *
 * @since 1.0
 */
class ComposerInstaller
{
	static protected $binFile = <<<BIN
#!/usr/bin/env sh
<?php

include_once dirname(__DIR__) . '/libraries/windwalker/bin/windwalker.php';

BIN;

	/**
	 * install
	 *
	 * @param CommandEvent $event
	 *
	 * @return  void
	 */
	public static function install(CommandEvent $event)
	{
		$windPath = getcwd();

		$io = $event->getIO();

		// Create console file.
		$io->write('Writing console file to bin.');

		file_put_contents($windPath . '/../../bin/windwalker', static::$binFile);

		// Config file
		$io->write('Prepare config file.');

		copy($windPath . '/config.dist.json', $windPath . 'config.json');

		// Bundles dir
		$bundlesDir = dirname($windPath) . '/windwalker-bundles';

		$io->write('Create bundle folder: ' . $bundlesDir);

		mkdir($bundlesDir);

		// Complete
		$io->write('Install complete.');
	}
}
