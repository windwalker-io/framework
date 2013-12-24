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
		$container->share('joomla.config', array('JFactory', 'getConfig'));

		$container->share('JApplicationCms', array('JFactory', 'getApplication'))
			->alias('app', 'JApplicationCms');

		$container->share('JDatabaseDriver', array('JFactory', 'getDbo'))
			->alias('db', 'JDatabaseDriver');

		$container->share('JUser', array('JFactory', 'getUser'))
			->alias('user', 'JUser');

		$container->share('JInput', \JFactory::getApplication()->input)
			->alias('input', 'JInput');

		$container->share('JEventDispatcher', array('JEventDispatcher', 'getInstance'))
			->alias('event.dispatcher', 'JEventDispatcher');
	}
}
