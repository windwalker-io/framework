<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Query;

/**
 * Class QueryBuilder
 *
 * @since 2.0
 */
abstract class AbstractQueryBuilder implements QueryBuilderInterface
{
	/**
	 * Property query.
	 *
	 * @var  Query
	 */
	public static $query = null;

	/**
	 * Property instance.
	 *
	 * @var  QueryBuilderInterface[]
	 */
	protected static $instance = array();

	/**
	 * getInstance
	 *
	 * @param   string  $name
	 *
	 * @return  QueryBuilderInterface
	 */
	public static function getInstance($name)
	{
		if (!isset(static::$instance[strtolower($name)]))
		{
			$name = ucfirst($name);

			static::$instance[strtolower($name)] = sprintf(__NAMESPACE__ . '\%s\%sQueryBuilder', $name, $name);
		}

		return static::$instance[strtolower($name)];
	}

	/**
	 * build
	 *
	 * @return  string
	 */
	public static function build()
	{
		$args = func_get_args();

		$sql = array();

		foreach ($args as $arg)
		{
			if ($arg === '' || $arg === null || $arg === false)
			{
				continue;
			}

			$sql[] = $arg;
		}

		return implode(' ', $args);
	}

	/**
	 * getQuery
	 *
	 * @param bool $new
	 *
	 * @return  Query
	 */
	public static function getQuery($new = false)
	{
		if (!static::$query || $new)
		{
			static::$query = new Query;
		}

		return static::$query;
	}
}

