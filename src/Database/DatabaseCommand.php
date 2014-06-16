<?php
/**
 * Part of datamapper project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database;

use Joomla\Database\DatabaseDriver;

/**
 * Some Useful function for database operation.
 */
class DatabaseCommand
{
	/**
	 * Database adapter.
	 *
	 * @var  DatabaseDriver
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
	 * @param DatabaseDriver $db Database adapter.
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->db = $db;
	}

	/**
	 * Batch update some data.
	 *
	 * @param string $table      Table name.
	 * @param string $data       Data you want to update.
	 * @param mixed  $conditions Where conditions, you can use array or Compare object.
	 *                           Example:
	 *                           - `array('id' => 5)` => id = 5
	 *                           - `new GteCompare('id', 20)` => 'id >= 20'
	 *                           - `new Compare('id', '%Flower%', 'LIKE')` => 'id LIKE "%Flower%"'
	 *
	 * @return  boolean True if update success.
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

		$query->update($table);

		return $this->db->setQuery($query)->execute();
	}

	/**
	 * Get table columns.
	 *
	 * @param string $table Table name.
	 *
	 * @return  array Table columns with type.
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
	 * Get db object.
	 *
	 * @return  DatabaseDriver Database adapter.
	 */
	public function getDb()
	{
		return $this->db;
	}

	/**
	 * Set db object
	 *
	 * @param   DatabaseDriver $db  Database adapter.
	 *
	 * @return  DatabaseCommand  Return self to support chaining.
	 */
	public function setDb($db)
	{
		$this->db = $db;

		return $this;
	}
}
