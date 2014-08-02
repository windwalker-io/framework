<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Database\Command;

use Windwalker\Database\Driver\DatabaseAwareTrait;
use Windwalker\Database\Driver\DatabaseDriver;

/**
 * Class DatabaseDatabase
 *
 * @since 1.0
 */
abstract class DatabaseDatabase
{
	use DatabaseAwareTrait
	{
		DatabaseAwareTrait::__construct as doConstruct;
	}

	protected $database = null;

	/**
	 * Constructor.
	 *
	 * @param string         $database
	 * @param DatabaseDriver $db
	 */
	public function __construct($database, DatabaseDriver $db)
	{
		$this->database = $database;

		$this->doConstruct($db);
	}

	/**
	 * select
	 *
	 * @return  static
	 */
	abstract public function select();

	/**
	 * createDatabase
	 *
	 * @param string $name
	 *
	 * @return  mixed
	 */
	abstract public function create($name);

	/**
	 * dropDatabase
	 *
	 * @param bool $ifExists
	 *
	 * @return  mixed
	 */
	abstract public function drop($ifExists = false);

	/**
	 * renameDatabase
	 *
	 * @param string $newName
	 *
	 * @return  mixed
	 */
	abstract public function rename($newName);

	/**
	 * Method to get an array of all tables in the database.
	 *
	 * @param bool $refresh
	 *
	 * @return  array  An array of all the tables in the database.
	 *
	 * @since   1.0
	 */
	abstract public function getTables($refresh = false);

	/**
	 * getTableDetails
	 *
	 * @param bool $full
	 *
	 * @return  mixed
	 */
	abstract public function getTableDetails($full = true);

	/**
	 * getTableDetail
	 *
	 * @param bool $table
	 * @param bool $full
	 *
	 * @return  mixed
	 */
	abstract public function getTableDetail($table, $full = true);
}

