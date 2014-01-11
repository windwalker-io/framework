<?php

namespace Windwalker\Model\Helper;

use JDatabaseDriver;
use JDatabaseQuery;
use JFactory;

/**
 * Class QueryHelper
 *
 * @since 1.0
 */
class QueryHelper
{
	const COLS_WITH_FIRST = 1;

	const COLS_PREFIX_WITH_FIRST = 2;

	/**
	 * A cache to store Table columns.
	 *
	 * @var array
	 */
	protected $columnCache;

	/**
	 * Property db.
	 *
	 * @var  JDatabaseDriver
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
	 * @param JDatabaseDriver $db
	 */
	public function __construct(JDatabaseDriver $db = null)
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

		if ($condition)
		{
			$condition = (string) new \JDatabaseQueryElement('ON', (array) $condition, ' AND ');
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
			if (empty($this->columnCache[$table['name']]))
			{
				$this->columnCache[$table['name']] = $this->db->getTableColumns($table['name']);
			}

			$columns = $this->columnCache[$table['name']];

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
	 * getFilterFields
	 *
	 * @return  array
	 */
	public function getFilterFields()
	{
		$fields = array();

		foreach ($this->tables as $alias => $table)
		{
			if (empty($this->columnCache[$table['name']]))
			{
				$this->columnCache[$table['name']] = $this->db->getTableColumns($table['name']);
			}

			$columns = $this->columnCache[$table['name']];

			foreach ($columns as $key => $var)
			{
				$fields[] = "{$alias}.{$key}";
			}
		}

		return $fields;
	}

	/**
	 * registerQueryTables
	 *
	 * @param JDatabaseQuery $query
	 *
	 * @return  JDatabaseQuery
	 */
	public function registerQueryTables(JDatabaseQuery $query)
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
	 * Get a query string to filter the publishing items now.
	 *
	 * Will return: "( publish_up < 'xxxx-xx-xx' OR publish_up = '0000-00-00' )
	 *                     AND ( publish_down > 'xxxx-xx-xx' OR publish_down = '0000-00-00' )"
	 *
	 * @param   string $prefix Prefix to columns name, eg: 'a.' will use `a`.`publish_up`.
	 *
	 * @return  string Query string.
	 */
	public static function publishingPeriod($prefix = '')
	{
		$db       = JFactory::getDbo();
		$nowDate  = $date = JFactory::getDate('now', JFactory::getConfig()->get('offset'))->toSQL();
		$nullDate = $db->getNullDate();

		$date_where = " ( {$prefix}publish_up < '{$nowDate}' OR  {$prefix}publish_up = '{$nullDate}') AND " .
			" ( {$prefix}publish_down > '{$nowDate}' OR  {$prefix}publish_down = '{$nullDate}') ";

		return $date_where;
	}

	/**
	 * Get a query string to filter the publishing items now, and the published > 0.
	 *
	 * Will return: "( publish_up < 'xxxx-xx-xx' OR publish_up = '0000-00-00' )
	 *                     AND ( publish_down > 'xxxx-xx-xx' OR publish_down = '0000-00-00' )
	 *                     AND published >= '1' "
	 *
	 * @param   string $prefix        Prefix to columns name, eg: 'a.' will use `a.publish_up`.
	 * @param   string $published_col The published column name. Usually 'published' or 'state' for com_content.
	 *
	 * @return  string    Query string.
	 */
	public static function publishingItems($prefix = '', $published_col = 'published')
	{
		return self::publishingPeriod($prefix) . " AND {$prefix}{$published_col} >= '1' ";
	}

	/**
	 * getDb
	 *
	 * @return  \JDatabaseDriver
	 */
	public function getDb()
	{
		if (!$this->db)
		{
			$this->db = \JFactory::getDbo();
		}

		return $this->db;
	}

	/**
	 * setDb
	 *
	 * @param   \JDatabaseDriver $db
	 *
	 * @return  QueryHelper  Return self to support chaining.
	 */
	public function setDb($db)
	{
		$this->db = $db;

		return $this;
}
}
