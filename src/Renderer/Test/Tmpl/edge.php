<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

use Windwalker\Cache\Storage\RawFileStorage;

include_once __DIR__ . '/../../../../vendor/autoload.php';

ini_set('memory_limit', '128M');

$finder = new \Windwalker\Edge\Loader\EdgeFileLoader;

$finder->addPath(__DIR__ . '/edge');

$edge = new \Windwalker\Edge\EdgeEnvironment($finder, new \Windwalker\Edge\Compiler\EdgeCompiler);

$storage = new RawFileStorage(__DIR__ . '/cache');

$storage->denyAccess(false);
$storage->setFileFormat('.php');

$edge->setCacheStorage($storage);

echo $edge->render('hello');
