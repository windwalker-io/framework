<?php

namespace Windwalker\Component;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Class ComponentProvider
 *
 * @since 1.0
 */
class ComponentProvider implements ServiceProviderInterface
{

	/**
	 * Registers the service provider within a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 */
	public function register(Container $container)
	{
		$container->share('JModel',
			function()
			{
				$class = '\\Windwalker\\Model\\Model';

				return new $class;
			}
		);
	}
}
