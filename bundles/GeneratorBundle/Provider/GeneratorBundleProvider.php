<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Provider;

use GeneratorBundle\IO\IO;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Windwalker\Console\Command\Command;

/**
 * Class GeneratorBundleProvider
 *
 * @since 1.0
 */
class GeneratorBundleProvider implements ServiceProviderInterface
{
	/**
	 * Property command.
	 *
	 * @var Command
	 */
	protected $command;

	/**
	 * Constructor.
	 *
	 * @param Command $command
	 */
	public function __construct(Command $command)
	{
		$this->command = $command;
	}

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
		$ioClass = 'GeneratorBundle\\IO\\IO';

		$container->alias('io', $ioClass)
			->alias('CodeGenerator\\IO\\IO', $ioClass)
			->alias('CodeGenerator\\IO\\IOInterface', $ioClass)
			->share($ioClass, new IO($this->command));
	}
}
