<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Driver\Postgresql;

use Windwalker\Database\Command\AbstractDatabase;
use Windwalker\Query\Postgresql\PostgresqlQueryBuilder;

/**
 * Class PostgresqlDatabase
 *
 * @since 2.0
 */
class PostgresqlDatabase extends AbstractDatabase
{
	/**
	 * select
	 *
	 * @return  static
	 */
	public function select()
	{
		$this->db->disconnect();

		$this->db->setDatabaseName($this->getName());

		return $this;
	}

	/**
	 * createDatabase
	 *
	 * @param bool   $ifNotExists
	 * @param string $charset
	 *
	 * @return  static
	 */
	public function create($ifNotExists = false, $charset = 'utf8')
	{
		$query = PostgresqlQueryBuilder::createDatabase($this->database, $charset);

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
		if ($this->getName() == $this->db->getDatabase()->getName())
		{
			$this->db->disconnect();
			$this->db->setDatabaseName(null);
			$this->db->connect();
		}

		$pid = version_compare($this->db->getVersion(), '9.2', '>=') ? 'pid' : 'procpid';

		$query = $this->db->getQuery(true);

		$query->select('pg_terminate_backend(' . $pid . ')')
			->from('pg_stat_activity')
			->where('datname = ' . $query->quote($this->getName()));

		$this->db->setQuery($query)->execute();

		$query = PostgresqlQueryBuilder::dropDatabase($this->database, $ifExists);

		$this->db->setQuery($query)->execute();

		return $this;
	}

	/**
	 * exists
	 *
	 * @return  boolean
	 */
	public function exists()
	{
		$databases = $this->db->listDatabases();

		return in_array($this->database, $databases);
	}

	/**
	 * renameDatabase
	 *
	 * @param string  $newName
	 * @param boolean $returnNew
	 *
	 * @return  static
	 */
	public function rename($newName, $returnNew = true)
	{
		if ($this->db->getDatabase()->getName() == $this->getName())
		{
			$this->db->disconnect();
			$this->db->setDatabaseName(null);
		}

		$pid = version_compare($this->db->getVersion(), '9.2', '>=') ? 'pid' : 'procpid';

		$query = $this->db->getQuery(true);

		$query->select('pg_terminate_backend(' . $pid . ')')
			->from('pg_stat_activity')
			->where('datname = ' . $query->quote($this->getName()));

		$this->db->setQuery($query)->execute();

		$query = sprintf(
			'ALTER DATABASE %s RENAME TO %s',
			$this->db->quoteName($this->getName()),
			$this->db->quoteName($newName)
		);

		$this->db->setQuery($query)->execute();

		if ($returnNew)
		{
			return $this->db->getDatabase($newName)->select();
		}

		return $this;
	}

	/**
	 * Method to get an array of all tables in the database.
	 *
	 * @param bool $refresh
	 *
	 * @return  array  An array of all the tables in the database.
	 *
	 * @since   2.0
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
	 * @return  object[]
	 */
	public function getTableDetails()
	{
		if (isset(static::$tableDetailsCache[$this->database]))
		{
			return static::$tableDetailsCache[$this->database];
		}

		$query = PostgresqlQueryBuilder::showDbTables($this->database);

		$details = $this->db->setQuery($query)->loadAll('Name');

		return static::$tableDetailsCache[$this->database] = $details;
	}

	/**
	 * getTableDetail
	 *
	 * @param bool $table
	 *
	 * @return  mixed
	 */
	public function getTableDetail($table)
	{
		$tables = $this->getTableDetails();

		$table = $this->db->replacePrefix($table);

		if (!isset($tables[$table]))
		{
			return false;
		}

		return $tables[$table];
	}

	/**
	 * tableExists
	 *
	 * @param string $table
	 *
	 * @return  boolean
	 */
	public function tableExists($table)
	{
		return (bool) $this->getTableDetail($table);
	}
}

