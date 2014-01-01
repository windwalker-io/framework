<?php

namespace Windwalker\Helper;

/**
 * Class QueryHelper
 *
 * @since 1.0
 */
class QueryHelper
{
	/**
	 * A cache to store Table columns.
	 *
	 * @var array
	 */
	static protected $columns;

	/**
	 * Get select query from tables array.
	 *
	 * @param   \JDatabaseDriver $db       Db object.
	 * @param   array            $tables   Tables name to get columns.
	 * @param   boolean          $noprefix The first table will not add prefix.
	 *
	 * @return  array    Select column list.
	 */
	public static function getSelectList($db, $tables = array(), $noprefix = true)
	{
		$select = array();
		$fields = array();
		$db     = $db ?: \JFactory::getDbo();

		$i = 0;

		foreach ($tables as $alias => $table)
		{
			if (empty(self::$columns[$table]))
			{
				self::$columns[$table] = $db->getTableColumns($table);
			}

			$columns = self::$columns[$table];

			foreach ($columns as $column => $var)
			{
				if ($i === 0 && $noprefix)
				{
					$fields[] = $db->quoteName("{$alias}.{$column}", $column);
					$fields[] = $db->quoteName("{$alias}.{$column}", "{$alias}_{$column}");
				}
				else
				{
					$fields[] = $db->quoteName("{$alias}.{$column}", "{$alias}_{$column}");
				}
			}

			$i++;
		}

		return $fields;
	}

	/**
	 * Merge filter_fields with table columns.
	 *
	 * @param   \JDatabaseDriver $db            Db object.
	 * @param   array            $filter_fields Filter fields from Model.
	 * @param   array            $tables        Tables name to get columns.
	 * @param   array            $option        Options.
	 *
	 * @return   array    Filter fields list.
	 */
	public static function mergeFilterFields($db, $filter_fields, $tables = array(), $option = array())
	{
		$fields = array();
		$db     = $db ?: \JFactory::getDbo();
		$ignore = array(
			'params'
		);

		// Ignore some columns
		if (!empty($option['ignore']))
		{
			$ignore = array_merge($ignore, $option['ignore']);
		}

		foreach ($tables as $alias => $table)
		{
			if (empty(self::$columns[$table]))
			{
				self::$columns[$table] = $db->getTableColumns($table);
			}

			$columns = self::$columns[$table];

			foreach ($columns as $key => $var)
			{
				if (in_array($key, $ignore))
				{
					continue;
				}

				$fields[] = "{$alias}.{$key}";
			}
		}

		return array_merge($filter_fields, $fields);
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
}
