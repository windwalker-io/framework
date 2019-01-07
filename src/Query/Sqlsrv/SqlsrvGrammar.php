<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Query\Sqlsrv;

use Windwalker\Query\AbstractQueryGrammar;
use Windwalker\Query\QueryElement;

/**
 * The SqlsrvGrammar class.
 *
 * @since  __DEPLOY_VERSION__
 */
class SqlsrvGrammar extends AbstractQueryGrammar
{
    /**
     * showDatabases
     *
     * @param null $where
     *
     * @return  string
     */
    public static function listDatabases($where = null)
    {
        $where = $where ? new QueryElement('WHERE', $where, 'AND') : null;

        return 'SELECT name FROM master.dbo.sysdatabases ' . $where;
    }

    /**
     * createDatabase
     *
     * @param string $name
     * @param string $collate
     *
     * @return  string
     */
    public static function createDatabase($name, $collate = null)
    {
        $query = static::getQuery();

        return static::build(
            'CREATE DATABASE',
            $query->quoteName($name),
            $collate ? 'COLLATE ' . $collate : null
        );
    }

    /**
     * dropTable
     *
     * @param string $name
     * @param bool   $ifExists
     *
     * @return  string
     */
    public static function dropDatabase($name, $ifExists = null)
    {
        $query = static::getQuery();

        return static::build(
            'DROP DATABASE',
            $ifExists ? 'IF EXISTS' : null,
            $query->quoteName($name)
        );
    }

    /**
     * showTableColumn
     *
     * @param string $table
     *
     * @return  string
     */
    public static function showTableColumns($table)
    {

    }

    /**
     * showDbTables
     *
     * @param string $dbname
     *
     * @return  string
     */
    public static function showDbTables($dbname)
    {
        $query = self::getQuery(true);

        return $query->format('SELECT name AS Name FROM %n.sys.Tables WHERE type = %q', $dbname, 'U');
    }

    /**
     * createTable
     *
     * @param string $name
     * @param array $columns
     *
     * @return  string
     */
    public static function createTable($name, $columns)
    {

    }

    /**
     * dropTable
     *
     * @param string $table
     *
     * @return  string
     */
    public static function dropTable($table)
    {

    }

    /**
     * Add column
     *
     * @param string $table
     * @param string $column
     * @param string $type
     *
     * @return  string
     */
    public static function addColumn($table, $column, $type = 'text')
    {

    }

    /**
     * changeColumn
     *
     * @param string $table
     * @param string $oldColumn
     * @param string $newColumn
     * @param string $type
     *
     * @return  string
     */
    public static function changeColumn($table, $oldColumn, $newColumn, $type = 'text')
    {

    }

    /**
     * dropColumn
     *
     * @param string $table
     * @param string $column
     *
     * @return  string
     */
    public static function dropColumn($table, $column)
    {

    }

    /**
     * addIndex
     *
     * @param string $table
     * @param string $type
     * @param array $columns
     * @param string $name
     *
     * @return string
     */
    public static function addIndex($table, $type, $columns, $name)
    {

    }

    /**
     * getQuery
     *
     * @param bool $new
     *
     * @return  SqlsrvQuery
     */
    public static function getQuery($new = false)
    {
        if (!static::$query || $new) {
            static::$query = new SqlsrvQuery();
        }

        return static::$query;
    }
}
