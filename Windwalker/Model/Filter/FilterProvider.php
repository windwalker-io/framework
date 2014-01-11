<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Model\Filter;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Class FilterProvider
 *
 * @since 1.0
 */
class FilterProvider implements ServiceProviderInterface
{
	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = null;

	/**
	 * Constructor
	 *
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->name = strtolower($name);
	}

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container $container The DI container.
	 *
	 * @return  Container  Returns itself to support chaining.
	 */
	public function register(Container $container)
	{
		// QueryHelper
		$class = '\\Windwalker\\Model\\Helper\\QueryHelper';

		$container->alias('model.' . $this->name . '.helper.query', $class)
			->buildSharedObject($class);

		// Filter
		$filterClass = '\\Windwalker\\Model\\Filter\\FilterHelper';

		$container->alias('model.' . $this->name . '.filter', $filterClass)
			->alias('model.' . $this->name . '.helper.filter', $filterClass)
			->buildSharedObject($filterClass);

		// Search
		$searchClass = '\\Windwalker\\Model\\Filter\\SearchHelper';

		$container->alias('model.' . $this->name . '.search', $searchClass)
			->alias('model.' . $this->name . '.helper.search', $filterClass)
			->buildSharedObject($searchClass);
	}
}
