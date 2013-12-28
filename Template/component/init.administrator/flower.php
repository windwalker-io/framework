<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_flower
 * @copyright   Copyright (C) 2014 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

include_once JPATH_LIBRARIES . '/windwalker/Windwalker/init.php';
include_once __DIR__ . '/component.php';


// Legacy
define('AKDEBUG', true);

if (JDEBUG)
{
	JLoader::registerNamespace('Whoops', AKPATH_ADMIN . '/debugger');

	$whoops  = new Whoops\Run;
	$handler = new Whoops\Handler\PrettyPageHandler;

	$whoops->pushHandler($handler);
	$whoops->register();
}

JLoader::registerPrefix('Flower', JPATH_COMPONENT_ADMINISTRATOR);
JLoader::registerNamespace('Flower', JPATH_COMPONENT_ADMINISTRATOR . '/src');

// use Flower\Component\FlowerComponent;

echo $component = with(new FlowerComponent)->execute();
