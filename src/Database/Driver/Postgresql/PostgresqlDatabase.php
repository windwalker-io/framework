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
		$query = PostgresqlQueryBuilder::createDatabase($this->name, $charset);

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

		$query = PostgresqlQueryBuilder::dropDatabase($this->name, $ifExists);

		$this->db->setQuery($query)->execute();

		return $this;
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
}

