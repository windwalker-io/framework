<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DI\Test\Stub;

use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;

/**
 * The StubServiceProvider class.
 * 
 * @since  2.0
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
