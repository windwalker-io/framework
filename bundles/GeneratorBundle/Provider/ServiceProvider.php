<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Provider;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Class ServiceProvider
 *
 * @since 1.0
 */
class ServiceProvider implements ServiceProviderInterface
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
		$operators = array(
			'copy',
			'convert'
		);

		foreach ($operators as $operator)
		{
			$class = '\\GeneratorBundle\\FileOperator\\' . ucfirst($operator) . 'Operator';

			$container->alias('operator.' . $operator, $class)
				->buildSharedObject($class);
		}
	}
}
