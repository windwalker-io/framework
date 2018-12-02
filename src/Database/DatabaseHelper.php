<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Database;

use Windwalker\Database\Driver\AbstractDatabaseDriver;

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
     * @param   AbstractDatabaseDriver $db
     * @param   array|string           $queries
     *
     * @return  boolean
     */
    public static function batchQuery(AbstractDatabaseDriver $db, $queries)
    {
        if (is_string($queries)) {
            $queries = $db->splitSql($queries);
        }

        foreach ((array) $queries as $query) {
            if (!trim($query, " \n\r\t;")) {
                continue;
            }

            $db->setQuery($query)->execute();
        }

        return true;
    }
}
