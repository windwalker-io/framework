<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Query;

/**
 * Class QueryBuilder
 *
 * @since {DEPLOY_VERSION}
 */
interface QueryBuilderInterface
{
	/**
	 * showDatabases
	 *
	 * @return  string
	 */
	public static function showDatabases();

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
	 * @param string  $table
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
	 * @param string $name
	 * @param array  $columns
	 *
	 * @return  string
	 *
	 */
	public static function addIndex($table, $type, $name, $columns);

	/**
	 * dropIndex
	 *
	 * @param string $table
	 * @param string $type
	 * @param string $name
	 *
	 * @return  string
	 */
	public static function dropIndex($table, $type, $name);

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
}

