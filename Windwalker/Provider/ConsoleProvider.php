<?php

namespace Windwalker\Provider;

use Joomla\Console\Output\Stdout;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Input\Input;
use Windwalker\Console\Application\Console;

/**
 * Class SystemProvider
 *
 * @since 1.0
 */
class ConsoleProvider implements ServiceProviderInterface
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
		$container->alias('app', 'Windwalker\\Console\\Console')
			->share('Windwalker\\Console\\Console',
				function()
				{
					return new Console(null, null, new Stdout);
				}
			);

		// Database
		$container->alias('db', 'JDatabaseDriver')
			->share('JDatabaseDriver', array('JFactory', 'getDbo'));

		// Language
		$container->alias('language', 'JLanguage')
			->share('JLanguage', array('JFactory', 'getLanguage'));

		// User
		$container->alias('user', 'JUser')
			->share('JUser', \JFactory::getUser());

		// Input
		$container->alias('input', 'Joomla\\Input\\Input')
			->share(
				'Joomla\\Input\\Input',
				function()
				{
					return new Input;
				}
			);

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
	}
}
