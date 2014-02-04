<?php

namespace Windwalker\Provider;

use Joomla\Console\Output\Stdout;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Input\Input;
use Windwalker\Console\Application\Console;

/**
 * Class CliProvider
 *
 * @since 1.0
 */
class CliProvider implements ServiceProviderInterface
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
		$container->alias('app', 'Windwalker\\Console\\Application\\Console')
			->share('Windwalker\\Console\\Application\\Console',
				function($container)
				{
					return new Console(null, $container->get('windwalker.config'), new Stdout);
				}
			);

		// Input
		$container->alias('input', 'Joomla\\Input\\Cli')
			->buildSharedObject('Joomla\\Input\\Cli');
	}
}
