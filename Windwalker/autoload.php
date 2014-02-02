<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Load Composer
include_once dirname(__DIR__) . '/vendor/autoload.php';

// Load Joomla framework
JLoader::registerNamespace('Joomla', JPATH_LIBRARIES . '/framework');

// Load Windwalker framework
JLoader::registerNamespace('Windwalker', dirname(__DIR__));

// Load some file out of nameing standard
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');
