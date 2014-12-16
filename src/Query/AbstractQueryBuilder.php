<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
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

