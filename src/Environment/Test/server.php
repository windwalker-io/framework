<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

use Windwalker\Environment\Server;

$autoload = __DIR__ . '/../../../vendor/autoload.php';

if (!is_file($autoload))
{
	$autoload = __DIR__ . '/../vendor/autoload.php';
}

include_once $autoload;

$server = new Server;

echo $server->getRoot(false);
