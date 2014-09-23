<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\DI\Test\Stub;

use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;

/**
 * The StubServiceProvider class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class StubServiceProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container $container The DI container.
	 *
	 * @return  void
	 */
	public function register(Container $container)
	{
		$container->share(
			'bingo',
			function()
			{
				return 'Bingo';
			}
		);
	}
}
