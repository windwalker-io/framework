<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Database\Driver\Mysql;

use Windwalker\Database\Command\DatabaseDatabase;
use Windwalker\Query\Mysql\MysqlQueryBuilder;

/**
 * Class MysqlDatabase
 *
 * @since 1.0
 */
class MysqlDatabase extends DatabaseDatabase
{
	/**
	 * Property tablesCache.
	 *
	 * @var  array
	 */
	protected static $tablesCache = array();

	/**
	 * select
	 *
	 * @return  static
	 */
	public function select()
	{
		$this->db->setQuery('USE ' . $this->db->quoteName($this->database))->execute();

		return $this;
	}

	/**
	 * createDatabase
	 *
	 * @param bool   $ifNotExists
	 * @param string $charset
	 * @param string $collate
	 *
	 * @return  static
	 */
	public function create($ifNotExists = false, $charset = null, $collate = null)
	{
		$query = MysqlQueryBuilder::createDatabase($this->database, $ifNotExists, $charset, $collate);

		$this->db->setQuery($query)->execute();

		return $this;
	}

	/**
	 * dropDatabase
	 *
	 * @param bool $ifExists
	 *
	 * @return  static
	 */
	public function drop($ifExists = false)
	{
		$query = MysqlQueryBuilder::dropDatabase($this->database, $ifExists);

		$this->db->setQuery($query)->execute();

		return $this;
	}

	/**
	 * renameDatabase
	 *
	 * @param string $newName
	 *
	 * @return  static
	 */
	public function rename($newName)
	{
		$query = 'RENAME ' . $this->db->quoteName($this->database) . ' TO ' . $this->db->quoteName($newName);

		$this->db->setQuery($query)->execute();

		return $this;
	}

	/**
	 * Method to get an array of all tables in the database.
	 *
	 * @param bool $refresh
	 *
	 * @return  array  An array of all the tables in the database.
	 *
	 * @since   1.0
	 */
	public function getTables($refresh = false)
	{
		if (empty(static::$tablesCache) || $refresh)
		{
			static::$tablesCache = array_keys($this->getTableDetails(false));
		}

		return static::$tablesCache;
	}

	/**
	 * getTableDetails
	 *
	 * @param bool $full
	 *
	 * @return  object[]
	 */
	public function getTableDetails($full = true)
	{
		$query = MysqlQueryBuilder::showDbTables($this->database, $full);

		return $this->db->setQuery($query)->loadAll('Name');
	}

	/**
	 * getTableDetail
	 *
	 * @param bool $table
	 * @param bool $full
	 *
	 * @return  mixed
	 */
	public function getTableDetail($table, $full = true)
	{
		$query = MysqlQueryBuilder::showTableColumns($this->database, $full, 'Field = ' . $this->db->quote($table));

		return $this->db->setQuery($query)->loadOne();
	}
}

