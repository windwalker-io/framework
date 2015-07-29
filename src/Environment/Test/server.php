<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

use Windwalker\Environment\Server;

$autoload = __DIR__ . '/../../../vendor/autoload.php';

if (!is_file($autoload))
{
	$autoload = __DIR__ . '/../vendor/autoload.php';
}

include_once $autoload;

$server = new Server;

echo $server->getEntry();
echo "\n";
echo $server->getWorkingDirectory();

echo "\n\n";
