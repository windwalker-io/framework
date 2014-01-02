<?php

namespace Windwalker\Provider;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

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

		// Application
		$container->alias('app', 'JApplicationCms')
			->share('JApplicationCms', array('JFactory', 'getApplication'));

		// Database
		$container->alias('db', 'JDatabaseDriver')
			->share('JDatabaseDriver', array('JFactory', 'getDbo'));

		// Document
		$container->alias('document', 'JDocumentHtml')
			->share('JDocumentHtml', array('JFactory', 'getDocument'));

		// Language
		$container->alias('language', 'JLanguage')
			->share('JLanguage', array('JFactory', 'getLanguage'));

		// User
		$container->alias('user', 'JUser')
			->share('JUser', \JFactory::getUser());

		// Input
		$container->alias('input', 'JInput')
			->share('JInput', \JFactory::getApplication()->input);

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

		// Helpers
		$container->alias('helper.asset', '\\Windwalker\\Helper\\AssetHelper')
			->buildSharedObject('\\Windwalker\\Helper\\AssetHelper');
	}
}
