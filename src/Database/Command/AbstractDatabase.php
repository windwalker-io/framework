<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Command;

use Windwalker\Database\Driver\DatabaseAwareTrait;
use Windwalker\Database\Driver\AbstractDatabaseDriver;

/**
 * Class DatabaseDatabase
 *
 * @since 2.0
 */
abstract class AbstractDatabase
{
	/**
	 * Property database.
	 *
	 * @var  string
	 */
	protected $database = null;

	/**
	 * Property driver.
	 *
	 * @var  \Windwalker\Database\Driver\AbstractDatabaseDriver
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param string         $database
	 * @param AbstractDatabaseDriver $db
	 */
	public function __construct($database, AbstractDatabaseDriver $db)
	{
		$this->database = $database;

		$this->db = $db;
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
	 * @param bool   $ifNotExists
	 * @param string $charset
	 *
	 * @return  static
	 */
	abstract public function create($ifNotExists = false, $charset = null);

	/**
	 * dropDatabase
	 *
	 * @param bool $ifExists
	 *
	 * @return  static
	 */
	abstract public function drop($ifExists = false);

	/**
	 * exists
	 *
	 * @return  boolean
	 */
	abstract public function exists();

	/**
	 * renameDatabase
	 *
	 * @param string  $newName
	 * @param boolean $returnNew
	 *
	 * @return  static
	 */
	abstract public function rename($newName, $returnNew = true);

	/**
	 * Method to get an array of all tables in the database.
	 *
	 * @param bool $refresh
	 *
	 * @return  array  An array of all the tables in the database.
	 *
	 * @since   2.0
	 */
	abstract public function getTables($refresh = false);

	/**
	 * getTableDetails
	 *
	 * @return  mixed
	 */
	abstract public function getTableDetails();

	/**
	 * getTableDetail
	 *
	 * @param bool $table
	 *
	 * @return  mixed
	 */
	abstract public function getTableDetail($table);

	/**
	 * tableExists
	 *
	 * @param string $table
	 *
	 * @return  boolean
	 */
	abstract public function tableExists($table);

	/**
	 * Method to get property Table
	 *
	 * @return  string
	 */
	public function getName()
	{
		return $this->database;
	}

	/**
	 * Method to set property table
	 *
	 * @param   string $name
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setName($name)
	{
		$this->database = $name;

		return $this;
	}

	/**
	 * Method to get property Db
	 *
	 * @return  \Windwalker\Database\Driver\AbstractDatabaseDriver
	 */
	public function getDriver()
	{
		return $this->db;
	}

	/**
	 * Method to set property db
	 *
	 * @param   \Windwalker\Database\Driver\AbstractDatabaseDriver $db
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDriver($db)
	{
		$this->db = $db;

		return $this;
	}
}

