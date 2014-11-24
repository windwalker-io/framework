<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Form;

use Windwalker\Form\Filter\DefaultFilter;

/**
 * The FilterHelper class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class FilterHelper extends AbstractFormElementHelper
{
	/**
	 * Property fieldNamespaces.
	 *
	 * @var  \SplPriorityQueue
	 */
	protected static $namespaces = null;

	/**
	 * Property defaultNamespace.
	 *
	 * @var string
	 */
	protected static $defaultNamespace = 'Windwalker\\Form\\Filter';

	/**
	 * createFilter
	 *
	 * @param string            $filter
	 * @param \SplPriorityQueue $namespaces
	 *
	 * @return  bool|DefaultFilter
	 */
	public static function create($filter, \SplPriorityQueue $namespaces = null)
	{
		if (class_exists($filter))
		{
			return new $filter;
		}

		$namespaces = $namespaces ? : static::getNamespaces();

		foreach ($namespaces as $namespace)
		{
			$class = trim($namespace, '\\') . '\\' . ucfirst($filter) . 'Filter';

			if (class_exists($class))
			{
				return new $class;
			}
		}

		return new DefaultFilter($filter);
	}
}
