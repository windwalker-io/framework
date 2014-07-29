<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database\Driver\Mysql;

use Windwalker\Database\Command\DatabaseTable;
use Windwalker\Query\Mysql\MysqlQueryBuilder;

/**
 * Class MysqlTable
 *
 * @since 1.0
 */
class MysqlTable extends DatabaseTable
{
	/**
	 * A cache to store Table columns.
	 *
	 * @var array
	 */
	protected static $columnCache = array();

	/**
	 * Get table columns.
	 *
	 * @param bool $refresh
	 *
	 * @return  array Table columns with type.
	 */
	public function getColumns($refresh = false)
	{
		if (empty(self::$columnCache) || $refresh)
		{
			self::$columnCache = array_keys($this->getColumnDetails());
		}

		return self::$columnCache;
	}

	/**
	 * getColumnDetails
	 *
	 * @param bool $full
	 *
	 * @return  mixed
	 */
	public function getColumnDetails($full = false)
	{
		$query = MysqlQueryBuilder::showTableColumns($this->table, $full);

		return $this->db->setQuery($query)->loadAll('Field');
	}

	public function addColumn($name, $type = 'text', $unsigned = true, $notNull = false, $default = '', $position = null, $comment = '')
	{
		$query = MysqlQueryBuilder::addColumn($this->table, $name, $type, $unsigned, $notNull, $default, $position, $comment);
	}
}
 