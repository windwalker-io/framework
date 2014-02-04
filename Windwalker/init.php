<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use Windwalker\DI\Container;

$version = new JVersion;

if (!$version->isCompatible('3.2'))
{
	throw new Exception('Windwalker need Joomla! 3.2 or higher.');
}

// Import Windwalker autoload.
include_once __DIR__ . '/autoload.php';

include_once __DIR__ . '/PHP/methods.php';

define('WINDWALKER', dirname(__DIR__));

define('WINDWALKER_SOURCE', __DIR__);

define('WINDWALKER_BUNDLE', dirname(WINDWALKER) . '/windwalker-bundles');

// Register global provider
$container = Container::getInstance();

$container->registerServiceProvider(new \Windwalker\Provider\SystemProvider);

// Register bundles
$paths = new \Windwalker\Filesystem\Path\PathCollection(
	array(
		WINDWALKER . '/bundles',
		WINDWALKER_BUNDLE,
	)
);

$bundles = $paths->findAll('Bundle$');

$config = $container->get('windwalker.config');

foreach ($bundles as $bundle)
{
	$bundleName = $bundle->getBasename();

	$class = $bundleName . '\\' . $bundleName;

	\JLoader::registerNamespace($bundleName, dirname((string) $bundle));

	if (class_exists($class) && is_subclass_of($class, 'Windwalker\\Bundle\\AbstractBundle'))
	{
		$config->set('bundle.' . $bundleName, $class);

		$class::registerProvider($container);
	}
}

// Load language
$lang = JFactory::getLanguage();
$lang->load('lib_windwalker', JPATH_BASE, null, false, false)
|| $lang->load('lib_windwalker', WINDWALKER, null, false, false)
|| $lang->load('lib_windwalker', JPATH_BASE, $lang->getDefault(), false, false)
|| $lang->load('lib_windwalker', WINDWALKER, $lang->getDefault(), false, false);
