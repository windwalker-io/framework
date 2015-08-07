<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Query\Postgresql;

use Windwalker\Query\AbstractQueryBuilder;
use Windwalker\Query\Query;
use Windwalker\Query\QueryElement;

/**
 * Class PostgresqlQueryBuilder
 *
 * @since 2.0
 */
abstract class PostgresqlQueryBuilder extends AbstractQueryBuilder
{
	const PRIMARY  = 'PRIMARY KEY';
	const INDEX    = 'INDEX';
	const UNIQUE   = 'UNIQUE';
	const SPATIAL  = 'SPATIAL';
	const FULLTEXT = 'UNIQUE';
	const FOREIGN  = 'FOREIGN KEY';

	/**
	 * Property query.
	 *
	 * @var  Query
	 */
	public static $query = null;

	/**
	 * showDatabases
	 *
	 * @param array|string $where
	 *
	 * @return  string
	 */
	public static function listDatabases($where = null)
	{
		$where = (array) $where;
		$where[] = 'datistemplate = false';
		$where = new QueryElement('WHERE', $where, ' AND ');

		return 'SELECT datname FROM pg_database ' . $where . ';';
	}

	/**
	 * createDatabase
	 *
	 * @param string $name
	 * @param string $encoding
	 * @param string $owner
	 *
	 * @return  string
	 */
	public static function createDatabase($name, $encoding = null, $owner = null)
	{
		$query = static::getQuery();

		return static::build(
			'CREATE DATABASE',
			$query->quoteName($name),
			$encoding ? 'ENCODING ' . $query->quote($encoding) : null,
			$owner ? 'OWNER ' . $query->quoteName($owner) : null
		);
	}

	/**
	 * dropTable
	 *
	 * @param string $db
	 * @param bool   $ifExist
	 *
	 * @return  string
	 */
	public static function dropDatabase($db, $ifExist = false)
	{
		$query = static::getQuery();

		return static::build(
			'DROP DATABASE',
			$ifExist ? 'IF EXISTS' : null,
			$query->quoteName($db)
		);
	}

	/**
	 * showTableColumn
	 *
	 * @param string       $table
	 * @param bool         $full
	 * @param string|array $where
	 *
	 * @return  string
	 */
	public static function showTableColumns($table, $full = false, $where = null)
	{
		$query = static::getQuery(true);

		// Field
		$query->select('attr.attname AS "column_name"')
			->from('pg_catalog.pg_attribute AS attr')
			->leftJoin('pg_catalog.pg_class AS class', 'class.oid = attr.attrelid');

		// Type
		$query->select('pg_catalog.format_type(attr.atttypid, attr.atttypmod) AS "column_type"')
			->leftJoin('pg_catalog.pg_type AS typ', 'typ.oid = attr.atttypid');
		// Is Null
		$query->select('CASE WHEN attr.attnotnull IS TRUE THEN \'NO\' ELSE \'YES\' END AS "Null"');

		// Default
		$query->select('attrdef.adsrc AS "Default"')
			->leftJoin('pg_catalog.pg_attrdef AS attrdef', 'attr.attrelid = attrdef.adrelid AND attr.attnum = attrdef.adnum');

		// Extra / Comments
		$query->select('dsc.description AS "comments"')
			->leftJoin('pg_catalog.pg_description AS dsc', 'dsc.classoid = class.oid');

		// General
		$query->where('attr.attrelid = (SELECT oid FROM pg_catalog.pg_class WHERE relname=' . $query->quote($table) . '
	AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace WHERE
	nspname = \'public\'))')
			->where('attr.attnum > 0 AND NOT attr.attisdropped')
			->order('attr.attnum');

		return (string) $query;
	}

	/**
	 * showDbTables
	 *
	 * @param string $dbname
	 * @param string $where
	 *
	 * @return  string
	 */
	public static function showDbTables($dbname, $where = null)
	{
		$query = static::getQuery(true);

		$query->select('table_name')
			->from('information_schema.tables')
			->where('table_type=' . $query->quote('BASE TABLE'))
			->where('table_schema NOT IN (' . $query->quote('pg_catalog') . ', ' . $query->quote('information_schema') . ')')
			->order('table_name ASC');

		return (string) $query;
	}

	/**
	 * createTable
	 *
	 * @param string       $name
	 * @param array        $columns
	 * @param array|string $pks
	 * @param array        $keys
	 * @param null         $autoIncrement
	 * @param bool         $ifNotExists
	 * @param string       $engine
	 * @param string       $defaultCharset
	 *
	 * @throws \InvalidArgumentException
	 * @return  string
	 */
	public static function createTable($name, $columns, $pks = array(), $keys = array(), $autoIncrement = null,
		$ifNotExists = true, $engine = 'InnoDB', $defaultCharset = 'utf8')
	{
		$query = static::getQuery();
		$cols = array();
		$engine = $engine ? : 'InnoDB';

		foreach ($columns as $cName => $details)
		{
			$details = (array) $details;

			array_unshift($details, $query->quoteName($cName));

			$cols[] = call_user_func_array(array(get_called_class(), 'build'), $details);
		}

		if (!is_array($keys))
		{
			throw new \InvalidArgumentException('Keys should be an array');
		}

		if ($pks)
		{
			$pks = array(
				'type' => 'PRIMARY KEY',
				'columns' => (array) $pks
			);

			array_unshift($keys, $pks);
		}

		foreach ($keys as $key)
		{
			$define = array(
				'type' => 'KEY',
				'name' => null,
				'columns' => array(),
				'comment' => ''
			);

			if (!is_array($key))
			{
				throw new \InvalidArgumentException('Every key data should be an array with "type", "name", "columns"');
			}

			$define = array_merge($define, $key);

			$cols[] = $define['type'] . ' ' . static::buildIndexDeclare($define['name'], $define['columns']);
		}

		$cols = "(\n" . implode(",\n", $cols) . "\n)";

		return static::build(
			'CREATE TABLE',
			$ifNotExists ? 'IF NOT EXISTS' : null,
			$query->quoteName($name),
			$cols,
			'ENGINE=' . $engine,
			$autoIncrement ? 'AUTO_INCREMENT=' . $autoIncrement : null,
			$defaultCharset ? 'DEFAULT CHARSET=' . $defaultCharset : null
		);
	}

	/**
	 * dropTable
	 *
	 * @param string $table
	 * @param bool   $ifExist
	 * @param string $option
	 *
	 * @return  string
	 */
	public static function dropTable($table, $ifExist = false, $option = '')
	{
		$query = static::getQuery();

		return static::build(
			'DROP TABLE',
			$ifExist ? 'IF EXISTS' : null,
			$query->quoteName($table),
			$option
		);
	}

	/**
	 * alterColumn
	 *
	 * @param string $operation
	 * @param string $table
	 * @param string $column
	 * @param string $type
	 * @param bool   $unsigned
	 * @param bool   $notNull
	 * @param null   $default
	 * @param null   $position
	 * @param string $comment
	 *
	 * @return  string
	 */
	public static function alterColumn($operation, $table, $column, $type = 'text', $unsigned = false, $notNull = false, $default = null,
		$position = null, $comment = '')
	{
		$query = static::getQuery();

		$column = $query->quoteName((array) $column);

		return static::build(
			'ALTER TABLE',
			$query->quoteName($table),
			$operation,
			implode(' ', $column),
			$type ? : 'text',
			$unsigned ? 'UNSIGNED' : null,
			$notNull ? 'NOT NULL' : null,
			!is_null($default) ? 'DEFAULT ' . $query->quote($default) : null,
			$comment ? 'COMMENT ' . $query->quote($comment) : null,
			static::handleColumnPosition($position)
		);
	}

	/**
	 * Add column
	 *
	 * @param string $table
	 * @param string $column
	 * @param string $type
	 * @param bool   $signed
	 * @param bool   $allowNull
	 * @param string $default
	 * @param string $position
	 * @param string $comment
	 *
	 * @return  string
	 */
	public static function addColumn($table, $column, $type = 'text', $signed = false, $allowNull = false, $default = null,
		$position = null, $comment = '')
	{
		return static::alterColumn('ADD', $table, $column, $type, $signed, $allowNull, $default, $position, $comment);
	}

	/**
	 * changeColumn
	 *
	 * @param string $table
	 * @param string $oldColumn
	 * @param string $newColumn
	 * @param string $type
	 * @param bool   $signed
	 * @param bool   $allowNull
	 * @param null   $default
	 * @param string $position
	 * @param string $comment
	 *
	 * @return  string
	 */
	public static function changeColumn($table, $oldColumn, $newColumn, $type = 'text', $signed = false, $allowNull = false, $default = null,
		$position = null, $comment = '')
	{
		$column = array($oldColumn, $newColumn);

		return static::alterColumn('CHANGE', $table, $column, $type, $signed, $allowNull, $default, $position, $comment);
	}

	/**
	 * modifyColumn
	 *
	 * @param string $table
	 * @param string $column
	 * @param string $type
	 * @param bool   $unsigned
	 * @param bool   $notNull
	 * @param null   $default
	 * @param string $position
	 * @param string $comment
	 *
	 * @return  string
	 */
	public static function modifyColumn($table, $column, $type = 'text', $unsigned = false, $notNull = false, $default = null,
		$position = null, $comment = '')
	{
		return static::alterColumn('MODIFY', $table, $column, $type, $unsigned, $notNull, $default, $position, $comment);
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
		$query = static::getQuery();

		return static::build(
			'ALTER TABLE',
			$query->quoteName($table),
			'DROP',
			$query->quoteName($column)
		);
	}

	/**
	 * addIndex
	 *
	 * @param string       $table
	 * @param string       $type
	 * @param string       $name
	 * @param string|array $columns
	 * @param string       $comment
	 *
	 * @return  string
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function addIndex($table, $type, $name, $columns, $comment = null)
	{
		$query = static::getQuery();
		$cols  = static::buildIndexDeclare($name, $columns);

		$comment = $comment ? 'COMMENT ' . $query->quote($comment) : '';

		return static::build(
			'ALTER TABLE',
			$query->quoteName($table),
			'ADD',
			strtoupper($type),
			$cols,
			$comment
		);
	}

	/**
	 * buildIndexDeclare
	 *
	 * @param string $name
	 * @param array  $columns
	 *
	 * @return  string
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function buildIndexDeclare($name, $columns)
	{
		$query = static::getQuery();
		$cols  = array();

		foreach ((array) $columns as $key => $val)
		{
			if (is_numeric($key))
			{
				$cols[] = $query->quoteName($val);
			}
			else
			{
				if (!is_numeric($val))
				{
					$string = is_string($val) ? ' ' . $query->quote($val) : '';

					throw new \InvalidArgumentException(sprintf('Index length should be number, (%s)%s given.', gettype($val), $string));
				}

				$cols[] = $query->quoteName($key) . '(' . $val . ')';
			}
		}

		$cols = '(' . implode(', ', $cols) . ')';

		$name = $name ? $query->quoteName($name) . ' ' : '';

		return $name . $cols;
	}

	/**
	 * dropIndex
	 *
	 * @param string $table
	 * @param string $type
	 * @param string $name
	 *
	 * @return  string
	 */
	public static function dropIndex($table, $type, $name)
	{
		$query = static::getQuery();

		return static::build(
			'ALTER TABLE',
			$query->quoteName($table),
			'DROP',
			strtoupper($type),
			$query->quoteName($name)
		);
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
	 * handleColumnPosition
	 *
	 * @param string $position
	 *
	 * @return  string
	 */
	protected static function handleColumnPosition($position)
	{
		$query = static::getQuery();

		if (!$position)
		{
			return null;
		}

		$posColumn = '';

		$position = trim($position);

		if (strpos(strtoupper($position), 'AFTER') !== false)
		{
			list($position, $posColumn) = explode(' ', $position, 2);

			$posColumn = $query->quoteName($posColumn);
		}

		return $position . ' ' . $posColumn;
	}

	/**
	 * replace
	 *
	 * @param string $name
	 * @param array  $columns
	 * @param array  $values
	 *
	 * @return  string
	 */
	public static function replace($name, $columns = array(), $values = array())
	{
		$query = new PostgresqlQuery;

		$query = (string) $query->insert($query->quoteName($name))
			->columns($columns)
			->values($values);

		$query = substr(trim($query), 6);

		return 'REPLACE' . $query;
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
			static::$query = new PostgresqlQuery;
		}

		return static::$query;
	}
}

