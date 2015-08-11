<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Database;

use Windwalker\Database\Driver\DatabaseDriver;

/**
 * The DatabaseHelper class.
 * 
 * @since  2.1
 */
abstract class DatabaseHelper
{
	/**
	 * batchQuery
	 *
	 * @param   DatabaseDriver $db
	 * @param   array|string   $queries
	 *
	 * @return  boolean
	 */
	public static function batchQuery(DatabaseDriver $db, $queries)
	{
		if (is_string($queries))
		{
			$queries = $db->splitSql($queries);
		}

		foreach ((array) $queries as $query)
		{
			if (!trim($query))
			{
				continue;
			}

			$db->setQuery($query)->execute();
		}

		return true;
	}
}
