<?php

namespace Windwalker\Provider;

use Joomla\Console\Output\Stdout;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Input\Input;
use Windwalker\Console\Application\Console;

/**
 * Class WebProvider
 *
 * @since 1.0
 */
class WebProvider implements ServiceProviderInterface
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
		// Application
		$container->alias('app', 'JApplicationCms')
			->share('JApplicationCms', array('JFactory', 'getApplication'));

		// Document
		$container->alias('document', 'JDocumentHtml')
			->share('JDocumentHtml', array('JFactory', 'getDocument'));

		// User
		$container->alias('user', 'JUser')
			->share('JUser', \JFactory::getUser());

		// Input
		$container->alias('input', 'JInput')
			->share('JInput', \JFactory::getApplication()->input);

		// Helpers
		if (\JFactory::getApplication() instanceof \JApplicationCms)
		{
			$container->alias('helper.asset', '\\Windwalker\\Helper\\AssetHelper')
				->buildSharedObject('\\Windwalker\\Helper\\AssetHelper');
		}
	}
}
