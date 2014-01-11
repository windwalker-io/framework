<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

include_once JPATH_LIBRARIES . '/windwalker/Windwalker/init.php';

JLoader::registerPrefix('Flower', JPATH_COMPONENT);
JLoader::registerNamespace('Flower', JPATH_COMPONENT_ADMINISTRATOR . '/src');
JLoader::register('FlowerComponent', JPATH_COMPONENT . '/component.php');

echo with(new FlowerComponent)->execute();
