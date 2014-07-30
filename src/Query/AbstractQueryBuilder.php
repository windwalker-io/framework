<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Query;

/**
 * Class QueryBuilder
 *
 * @since 1.0
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
 