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
	 * Property component.
	 *
	 * @var
	 */
	private $component;

	/**
	 * Constructor.
	 *
	 * @param $name
	 * @param $option
	 * @param $prefix
	 */
	public function __construct($name, $component)
	{
		$this->name      = $name;
		$this->component = $component;
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

		// Component
		$container->alias('component', ucfirst($name) . 'Component')
			->share(ucfirst($name) . 'Component', $this->component);

		// ControllerResolver
		$resolverClass = '\\Windwalker\\Controller\\Resolver\\ControllerResolver';

		$container->alias('controller.resolver', $resolverClass)
			->share(
				$resolverClass,
				function($container) use($resolverClass)
				{
					return new $resolverClass($container->get('app'), $container);
				}
			);

		// Asset Helper
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
	}
}
