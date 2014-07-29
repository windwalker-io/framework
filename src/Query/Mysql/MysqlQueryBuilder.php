<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Query\Mysql;

use Windwalker\Query\Query;

/**
 * Class MysqlQueryBuilder
 *
 * @since 1.0
 */
class MysqlQueryBuilder
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
	 * @param string $like
	 *
	 * @return  string
	 */
	public static function showDatabases($like = null)
	{
		$query = static::getQuery();

		$like = $like ? ' LIKE ' . $query->quote($like) : null;

		return 'SHOW DATABASES' . $like;
	}

	/**
	 * showTableColumn
	 *
	 * @param string $table
	 * @param bool   $full
	 * @param string $like
	 *
	 * @return  string
	 */
	public static function showTableColumns($table, $full = false, $like = null)
	{
		$query = static::getQuery();

		return static::build(
			'SHOW',
			$full ? 'FULL' : false,
			'sCOLUMNS FROM',
			$query->quoteName($table),
			$like ? 'LIKE ' . $query->quote($like) : null
		);
	}

	/**
	 * showDbTables
	 *
	 * @param string $dbname
	 * @param bool   $full
	 * @param string $like
	 *
	 * @return  string
	 */
	public static function showDbTables($dbname, $full = false, $like = null)
	{
		$query = static::getQuery();

		return static::build(
			'SHOW',
			$full ? 'FULL' : false,
			'TABLES FROM',
			$query->quoteName($dbname),
			$like ? 'LIKE ' . $query->quote($like) : null
		);
	}

	/**
	 * createTable
	 *
	 * @param string        $name
	 * @param array         $columns
	 * @param array|string  $pks
	 * @param array         $keys
	 * @param bool          $ifNotExists
	 * @param string        $engine
	 * @param null          $autoIncrement
	 * @param string        $defaultCharset
	 *
	 * @return  string
	 */
	public static function createTable($name, $columns, $pks = array(), $keys = array(), $ifNotExists = true, $engine = 'InnoDB',
		$autoIncrement = null, $defaultCharset = 'utf8')
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
				'columns' => array()
			);

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
	 * changeColumn
	 *
	 * @param string $table
	 * @param string $oldColumn
	 * @param string $newColumn
	 * @param string $definition
	 * @param string $position
	 *
	 * @return  string
	 */
	public static function changeColumn($table, $oldColumn, $newColumn, $definition, $position = null)
	{
		$query = static::getQuery();

		return static::build(
			'ALTER TABLE',
			$query->quoteName($table),
			'CHANGE',
			$query->quoteName($oldColumn),
			$query->quoteName($newColumn),
			$definition,
			static::handleColumnPosition($position)
		);
	}

	/**
	 * modifyColumn
	 *
	 * @param string $table
	 * @param string $column
	 * @param string $definition
	 * @param string $position
	 *
	 * @return  string
	 */
	public static function modifyColumn($table, $column, $definition, $position = null)
	{
		$query = static::getQuery();

		return static::build(
			'ALTER TABLE',
			$query->quoteName($table),
			'MODIFY',
			$query->quoteName($column),
			$definition,
			static::handleColumnPosition($position)
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
	protected function handleColumnPosition($position)
	{
		$query = static::getQuery();

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
			static::$query = new MysqlQuery;
		}

		return static::$query;
	}
}
 