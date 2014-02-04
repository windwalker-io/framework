<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  {{extension.element.lower}}
 * @copyright   Copyright (C) 2014 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

include_once JPATH_LIBRARIES . '/windwalker/Windwalker/init.php';
include_once __DIR__ . '/component.php';


// Legacy
define('AKDEBUG', true);

if (JDEBUG)
{
	\Windwalker\Debugger\Debugger::registerWhoops();
}

JLoader::registerPrefix('{{extension.name.cap}}', JPATH_COMPONENT_ADMINISTRATOR);
JLoader::registerNamespace('{{extension.name.cap}}', JPATH_COMPONENT_ADMINISTRATOR . '/src');

// use {{extension.name.cap}}\Component\{{extension.name.cap}}Component;

echo $component = with(new {{extension.name.cap}}Component)->execute();
