<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_flower
 * @copyright   Copyright (C) 2014 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

include_once JPATH_LIBRARIES . '/windwalker/Windwalker/init.php';

JLoader::registerPrefix('Flower', JPATH_COMPONENT_ADMINISTRATOR);
JLoader::registerNamespace('Flower', JPATH_COMPONENT_ADMINISTRATOR . '/src');

use Flower\Component\FlowerComponent;

$component = new FlowerComponent;

echo $component->execute();
