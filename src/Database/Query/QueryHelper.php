<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\Query\QueryElement;
use Windwalker\Compare\Compare;

/**
 * Class QueryHelper
 */
class QueryHelper
{
	const COLS_WITH_FIRST = 1;

	const COLS_PREFIX_WITH_FIRST = 2;

	/**
	 * Property db.
	 *
	 * @var  DatabaseDriver
	 */
	protected $db = null;

	/**
	 * Property tables.
	 *
	 * @var  array
	 */
	protected $tables = array();

	/**
	 * Constructor.
	 *
	 * @param DatabaseDriver $db
	 */
	public function __construct(DatabaseDriver $db = null)
	{
		$this->db = $db ? : $this->getDb();
	}

	/**
	 * addTable
	 *
	 * @param string $alias
	 * @param string $table
	 * @param mixed  $condition
	 * @param string $joinType
	 *
	 * @return  QueryHelper
	 */
	public function addTable($alias, $table, $condition = null, $joinType = 'LEFT')
	{
		$tableStorage = array();

		$tableStorage['name'] = $table;
		$tableStorage['join']  = strtoupper($joinType);

		if (is_array($condition))
		{
			$condition = array($condition);
		}

		if ($condition)
		{
			$condition = (string) new QueryElement('ON', $condition, ' AND ');
		}
		else
		{
			$tableStorage['join'] = 'FROM';
		}

		// Remove too many spaces
		$condition = preg_replace('/\s(?=\s)/', '', $condition);

		$tableStorage['condition'] = trim($condition);

		$this->tables[$alias] = $tableStorage;

		return $this;
	}

	/**
	 * removeTable
	 *
	 * @param string $alias
	 *
	 * @return  $this
	 */
	public function removeTable($alias)
	{
		if (!empty($this->tables[$alias]))
		{
			unset($this->tables[$alias]);
		}

		return $this;
	}

	/**
	 * getFilterFields
	 *
	 * @param int $prefixFirst
	 *
	 * @return  array
	 */
	public function getSelectFields($prefixFirst = self::COLS_WITH_FIRST)
	{
		$fields = array();

		$i = 0;

		foreach ($this->tables as $alias => $table)
		{
			$columns = DatabaseFactory::getCommand()->getColumns($table['name']);

			foreach ($columns as $column => $var)
			{
				if ($i === 0)
				{
					if ($prefixFirst & self::COLS_WITH_FIRST)
					{
						$fields[] = $this->db->quoteName("{$alias}.{$column}", $column);
					}

					if ($prefixFirst & self::COLS_PREFIX_WITH_FIRST)
					{
						$fields[] = $this->db->quoteName("{$alias}.{$column}", "{$alias}_{$column}");
					}
				}
				else
				{
					$fields[] = $this->db->quoteName("{$alias}.{$column}", "{$alias}_{$column}");
				}
			}

			$i++;
		}

		return $fields;
	}

	/**
	 * registerQueryTables
	 *
	 * @param DatabaseQuery $query
	 *
	 * @return  DatabaseQuery
	 */
	public function registerQueryTables(DatabaseQuery $query)
	{
		foreach ($this->tables as $alias => $table)
		{
			if ($table['join'] == 'FROM')
			{
				$query->from($query->quoteName($table['name']) . ' AS ' . $query->quoteName($alias));
			}
			else
			{
				$query->join(
					$table['join'],
					$query->quoteName($table['name']) . ' AS ' . $query->quoteName($alias) . ' ' . $table['condition']
				);
			}
		}

		return $query;
	}

	/**
	 * buildConditions
	 *
	 * @param DatabaseQuery $query
	 * @param array         $conditions
	 *
	 * @return  DatabaseQuery
	 */
	public static function buildWheres(DatabaseQuery $query, array $conditions)
	{
		foreach ($conditions as $key => $value)
		{
			if (empty($value))
			{
				continue;
			}

			// If using Compare class, we convert it to string.
			if ($value instanceof Compare)
			{
				$query->where((string) static::buildCompare($key, $value, $query));
			}

			// If key is numeric, just send value to query where.
			elseif (is_numeric($key))
			{
				$query->where((string) $value);
			}

			// If is array or object, we use "IN" condition.
			elseif (is_array($value) || is_object($value))
			{
				$value = array_map(array($query, 'quote'), (array) $value);

				$query->where($query->quoteName($key) . new QueryElement('IN ()', $value, ','));
			}

			// Otherwise, we use equal condition.
			else
			{
				$query->where($query->format('%n = %q', $key, $value));
			}
		}

		return $query;
	}

	/**
	 * buildCompare
	 *
	 * @param string|int    $key
	 * @param Compare       $value
	 * @param DatabaseQuery $query
	 *
	 * @return  string
	 */
	public static function buildCompare($key, Compare $value, $query = null)
	{
		$query = $query ? : DatabaseFactory::getDbo()->getQuery(true);

		if (!is_numeric($key))
		{
			$value->setCompare1($key);
		}

		$value->setHandler(
			function($compare1, $compare2, $operator) use ($query)
			{
				return $query->format('%n ' . $operator . ' %q', $compare1, $compare2);
			}
		);

		return (string) $value;
	}

	/**
	 * getDb
	 *
	 * @return  DatabaseDriver
	 */
	public function getDb()
	{
		if (!$this->db)
		{
			$this->db = DatabaseFactory::getDbo();
		}

		return $this->db;
	}

	/**
	 * setDb
	 *
	 * @param   DatabaseDriver $db
	 *
	 * @return  QueryHelper  Return self to support chaining.
	 */
	public function setDb($db)
	{
		$this->db = $db;

		return $this;
	}
}
