<?php
/**
 * Part of datamapper project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\DataMapper\Database;

use Joomla\Database\DatabaseDriver;

/**
 * Class DatabaseCommand
 *
 * @since 1.0
 */
class DatabaseCommand
{
	/**
	 * Property db.
	 *
	 * @var  null
	 */
	protected $db = null;

	/**
	 * A cache to store Table columns.
	 *
	 * @var array
	 */
	protected static $columnCache;

	/**
	 * Constructor.
	 *
	 * @param DatabaseDriver $db
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->db = $db;
	}

	/**
	 * updateBatch
	 *
	 * @param string $table
	 * @param string $data
	 * @param array  $conditions
	 *
	 * @return  bool|mixed
	 */
	public function updateBatch($table, $data, $conditions = array())
	{
		$query = $this->db->getQuery(true);

		// Build conditions
		$query = QueryHelper::buildWheres($query, $conditions);

		// Build update values.
		$fields = array_keys($this->getColumns($table));

		$hasField = false;

		foreach ((array) $data as $field => $value)
		{
			if (!in_array($field, $fields))
			{
				continue;
			}

			$query->set($query->format('%n = %q', $field, $value));

			$hasField = true;
		}

		if (!$hasField)
		{
			return false;
		}

		$query->update($table)->where($conditions);

		return $this->db->setQuery($query)->execute();
	}

	/**
	 * getColumns
	 *
	 * @param string $table
	 *
	 * @return  array
	 */
	public function getColumns($table)
	{
		if (empty(self::$columnCache[$table]))
		{
			self::$columnCache[$table] = $this->db->getTableColumns($table);
		}

		return self::$columnCache[$table];
	}

	/**
	 * getDb
	 *
	 * @return  null
	 */
	public function getDb()
	{
		return $this->db;
	}

	/**
	 * setDb
	 *
	 * @param   null $db
	 *
	 * @return  DatabaseCommand  Return self to support chaining.
	 */
	public function setDb($db)
	{
		$this->db = $db;

		return $this;
	}
}
