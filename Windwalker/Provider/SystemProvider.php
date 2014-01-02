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

		$container->alias('app', 'JApplicationCms')
			->share('JApplicationCms', array('JFactory', 'getApplication'));

		$container->alias('db', 'JDatabaseDriver')
			->share('JDatabaseDriver', array('JFactory', 'getDbo'));

		$container->alias('document', 'JDocumentHtml')
			->share('JDocumentHtml', array('JFactory', 'getDocument'));

		$container->alias('language', 'JLanguage')
			->share('JLanguage', array('JFactory', 'getLanguage'));

		$container->alias('user', 'JUser')
			->share('JUser', \JFactory::getUser());

		$container->alias('input', 'JInput')
			->share('JInput', \JFactory::getApplication()->input);

		$container->alias('event.dispatcher', 'JEventDispatcher')
			->share('JEventDispatcher', array('JEventDispatcher', 'getInstance'));

		$container->alias('date', 'JDate')
			->set('JDate',
				function()
				{
					return \JFactory::getDate('now', \JFactory::getConfig()->get('offset'));
				}
			);
	}
}
