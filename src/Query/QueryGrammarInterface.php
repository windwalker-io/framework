<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Query;

/**
 * QueryGrammar interface
 *
 * @since 2.0
 */
interface QueryGrammarInterface
{
    /**
     * showDatabases
     *
     * @return  string
     */
    public static function listDatabases();

    /**
     * createDatabase
     *
     * @param string $name
     *
     * @return  string
     */
    public static function createDatabase($name);

    /**
     * dropTable
     *
     * @param string $name
     *
     * @return  string
     */
    public static function dropDatabase($name);

    /**
     * showTableColumn
     *
     * @param string $table
     *
     * @return  string
     */
    public static function showTableColumns($table);

    /**
     * showDbTables
     *
     * @param string $dbname
     *
     * @return  string
     */
    public static function showDbTables($dbname);

    /**
     * createTable
     *
     * @param string $name
     * @param array  $columns
     *
     * @return  string
     */
    public static function createTable($name, $columns);

    /**
     * dropTable
     *
     * @param string $table
     *
     * @return  string
     */
    public static function dropTable($table);

    /**
     * Add column
     *
     * @param string $table
     * @param string $column
     * @param string $type
     *
     * @return  string
     */
    public static function addColumn($table, $column, $type = 'text');

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
    public static function changeColumn($table, $oldColumn, $newColumn, $type = 'text');

    /**
     * dropColumn
     *
     * @param string $table
     * @param string $column
     *
     * @return  string
     */
    public static function dropColumn($table, $column);

    /**
     * addIndex
     *
     * @param string $table
     * @param string $type
     * @param array  $columns
     * @param string $name
     *
     * @return string
     */
    public static function addIndex($table, $type, $columns, $name);

    /**
     * build
     *
     * @return  string
     */
    public static function build();

    /**
     * getQuery
     *
     * @param bool $new
     *
     * @return  Query
     */
    public static function getQuery($new = false);

    /**
     * buildJsonSelector
     *
     * @param  string  $column
     * @param  array   $paths
     * @param  bool    $unQuoteLast
     *
     * @return  string
     *
     * @since  3.5.21
     */
    public static function buildJsonSelector(string $column, array $paths, bool $unQuoteLast = true): string;
}
