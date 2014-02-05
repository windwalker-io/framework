<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  {{extension.element.lower}}
 * @copyright   Copyright (C) 2014 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

include_once JPATH_LIBRARIES . '/windwalker/Windwalker/init.php';

JLoader::registerPrefix('{{extension.name.cap}}', JPATH_COMPONENT);
JLoader::registerNamespace('{{extension.name.cap}}', JPATH_COMPONENT_ADMINISTRATOR . '/src');
JLoader::register('{{extension.name.cap}}Component', JPATH_COMPONENT . '/component.php');

echo with(new {{extension.name.cap}}Component)->execute();
