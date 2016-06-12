<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

include_once __DIR__ . '/../../../../vendor/autoload.php';

$finder = new \Windwalker\Edge\Loader\EdgeFileLoader;

$finder->addPath(__DIR__ . '/blade');

$edge = new \Windwalker\Edge\EdgeEnvironment($finder, new \Windwalker\Edge\Compiler\EdgeCompiler);

echo $edge->render('hello');
