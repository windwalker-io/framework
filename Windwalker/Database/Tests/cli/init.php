<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

include __DIR__ . '/../../vendor/autoload.php';

// Make the database driver.
$db = \Windwalker\Database\DatabaseFactory::getDbo(
	array(
		'driver' => 'mysql',
		'host'   => 'localhost',
		'user'   => 'root',
		'password' => '1234',
		'port'   => null,
		'socket' => null,
		'database' => 'joomla321',
		'prefix' => 'j321_'
	)
);
