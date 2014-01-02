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
	 * Property name.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Constructor.
	 *
	 * @param $name
	 * @param $option
	 * @param $prefix
	 */
	public function __construct($name)
	{
		$this->name = $name;
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
		$name = $this->name;

		// Helpers
		$container->extend(
			'\\Windwalker\\Helper\\AssetHelper',
			function($asset, $container) use($name)
			{
				$asset = clone $asset;

				return $asset->setName('com_' . strtolower($name))
					->setContainer($container);
			}
		);

		$container->alias('helper.asset', '\\Windwalker\\Helper\\AssetHelper');

		// Bind default model
		$modelName = '\\Windwalker\\Model\\Model';

		$container->alias('model', $modelName)
			->alias('JModel', $modelName)
			->buildSharedObject($modelName);

		$container->get('JModel')
			->setName('default')
			->setOption('com_' . strtolower($name));
	}
}
