<?php

namespace Windwalker\Provider;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Registry\Registry;

/**
 * Class SystemProvider
 *
 * @since 1.0
 */
class SystemProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container $container The DI container.
	 *
	 * @return  Container  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function register(Container $container)
	{
		// Global Config
		$container->share('joomla.config', array('JFactory', 'getConfig'));

		// Windwalker Config
		$container->share('windwalker.config', array($this, 'loadConfig'));

		// Database
		$container->alias('db', 'JDatabaseDriver')
			->share('JDatabaseDriver', array('JFactory', 'getDbo'));

		// Language
		$container->alias('language', 'JLanguage')
			->share('JLanguage', array('JFactory', 'getLanguage'));

		// Dispatcher
		$container->alias('event.dispatcher', 'JEventDispatcher')
			->share('JEventDispatcher', array('JEventDispatcher', 'getInstance'));

		// Date
		$container->alias('date', 'JDate')
			->set('JDate',
				function()
				{
					return \JFactory::getDate('now', \JFactory::getConfig()->get('offset'));
				}
			);

		// Global
		$container->set('SplPriorityQueue',
			function()
			{
				return new \SplPriorityQueue;
			}
		);

		// Detect deferent environment
		if (defined('WINDWALKER_CONSOLE'))
		{
			$container->registerServiceProvider(new CliProvider);
		}
		else
		{
			$container->registerServiceProvider(new WebProvider);
		}
	}

	/**
	 * loadConfig
	 *
	 * @return  Registry
	 */
	public function loadConfig()
	{
		$file = WINDWALKER . '/config.json';

		if (!is_file($file))
		{
			\JFile::copy(WINDWALKER . '/config.dist.json', $file);
		}

		$config = new Registry;

		return $config->loadFile($file, 'json');
	}
}
