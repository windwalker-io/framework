<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

include_once dirname(__DIR__) . '/vendor/autoload.php';

// First let's look to see if we have a DSN defined or in the environment variables.
if (defined('DB_HOST') || getenv('DB_HOST'))
{
	$dsn = defined('DB_HOST') ? DB_HOST : getenv('DB_HOST');
}
else
{
	return;
}

// Make the database driver.
\Windwalker\Database\DatabaseFactory::getDbo(
	array(
		'driver'   => 'mysql',
		'host'     => DB_HOST,
		'user'     => DB_USER,
		'password' => DB_PASSWD
	)
);
