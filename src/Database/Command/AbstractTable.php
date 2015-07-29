<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Command;

use Windwalker\Database\Driver\DatabaseDriver;
use Windwalker\Database\Driver\DatabaseAwareTrait;
use Windwalker\Database\Schema\Column;

/**
 * Class DatabaseTable
 *
 * @since 2.0
 */
abstract class AbstractTable
{
	/**
	 * Property table.
	 *
	 * @var  string
	 */
	protected $table = null;

	/**
	 * Property schema.
	 *
	 * @var  array
	 */
	protected $schema = array();

	/**
	 * Property queries.
	 *
	 * @var  array
	 */
	protected $queries = array();

	/**
	 * Property driver.
	 *
	 * @var  \Windwalker\Database\Driver\DatabaseDriver
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param string         $table
	 * @param DatabaseDriver $db
	 */
	public function __construct($table, DatabaseDriver $db)
	{
		$this->table = $table;

		$this->db = $db;
	}

	/**
	 * create
	 *
	 * @param bool  $ifNotExists
	 * @param array $options
	 *
	 * @return  static
	 */
	abstract public function create($ifNotExists = true, $options = array());

	/**
	 * update
	 *
	 * @return  static
	 */
	abstract public function update();

	/**
	 * save
	 *
	 * @param bool  $ifNotExists
	 * @param array $options
	 *
	 * @return  static
	 */
	abstract public function save($ifNotExists = true, $options = array());

	/**
	 * drop
	 *
	 * @param bool   $ifNotExists
	 * @param string $option
	 *
	 * @return  static
	 */
	abstract public function drop($ifNotExists = true, $option = '');

	/**
	 * reset
	 *
	 * @return  static
	 */
	abstract public function reset();

	/**
	 * exists
	 *
	 * @return  boolean
	 */
	abstract public function exists();

	/**
	 * create
	 *
	 * @param string $columns
	 * @param array  $pks
	 * @param array  $keys
	 * @param int    $autoIncrement
	 * @param bool   $ifNotExists
	 * @param string $engine
	 * @param string $defaultCharset
	 *
	 * @return  $this
	 */
	abstract public function doCreate($columns, $pks = array(), $keys = array(), $autoIncrement = null,
		$ifNotExists = true, $engine = 'InnoDB', $defaultCharset = 'utf8');

	/**
	 * rename
	 *
	 * @param string  $newName
	 * @param boolean $returnNew
	 *
	 * @return  $this
	 */
	abstract public function rename($newName, $returnNew = true);

	/**
	 * Locks a table in the database.
	 *
	 * @return  static  Returns this object to support chaining.
	 *
	 * @since   2.0
	 * @throws  \RuntimeException
	 */
	abstract public function lock();

	/**
	 * unlock
	 *
	 * @return  static  Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	abstract public function unlock();

	/**
	 * Method to truncate a table.
	 *
	 * @return  static
	 *
	 * @since   2.0
	 * @throws  \RuntimeException
	 */
	abstract public function truncate();

	/**
	 * Get table columns.
	 *
	 * @param bool $refresh
	 *
	 * @return  array Table columns with type.
	 */
	abstract public function getColumns($refresh = false);

	/**
	 * getColumnDetails
	 *
	 * @param bool $full
	 *
	 * @return  mixed
	 */
	abstract public function getColumnDetails($full = true);

	/**
	 * getColumnDetail
	 *
	 * @param string $column
	 * @param bool   $full
	 *
	 * @return  mixed
	 */
	abstract public function getColumnDetail($column, $full = true);

	/**
	 * addColumn
	 *
	 * @param string $name
	 * @param string $type
	 * @param bool   $signed
	 * @param bool   $allowNull
	 * @param string $default
	 * @param string $comment
	 * @param array  $options
	 *
	 * @return  static
	 */
	abstract public function addColumn($name, $type = 'text', $signed = true, $allowNull = true, $default = '', $comment = '', $options = array());

	/**
	 * dropColumn
	 *
	 * @param string $name
	 *
	 * @return  static
	 */
	abstract public function dropColumn($name);

	/**
	 * modifyColumn
	 *
	 * @param string|Column  $name
	 * @param string $type
	 * @param bool   $signed
	 * @param bool   $allowNull
	 * @param string $default
	 * @param string $comment
	 * @param array  $options
	 *
	 * @return  static
	 */
	abstract public function modifyColumn($name, $type = 'text', $signed = true, $allowNull = true, $default = '', $comment = '', $options = array());

	/**
	 * changeColumn
	 *
	 * @param string $oldName
	 * @param string|Column  $newName
	 * @param string $type
	 * @param bool   $signed
	 * @param bool   $allowNull
	 * @param string $default
	 * @param string $comment
	 * @param array  $options
	 *
	 * @return  static
	 */
	abstract public function changeColumn($oldName, $newName, $type = 'text', $signed = true, $allowNull = true, $default = '', $comment = '', $options = array());

	/**
	 * addIndex
	 *
	 * @param string $type
	 * @param string $name
	 * @param array  $columns
	 * @param string $comment
	 * @param array  $options
	 *
	 * @return  static
	 */
	abstract public function addIndex($type, $name = null, $columns = array(), $comment = null, $options = array());

	/**
	 * dropIndex
	 *
	 * @param string  $type
	 * @param string  $name
	 *
	 * @return  static
	 */
	abstract public function dropIndex($type, $name);

	/**
	 * getIndexes
	 *
	 * @return  static
	 */
	abstract public function getIndexes();

	/**
	 * Method to get property Table
	 *
	 * @return  string
	 */
	public function getName()
	{
		return $this->table;
	}

	/**
	 * Method to set property table
	 *
	 * @param   null|string $table
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setName($table)
	{
		$this->table = $table;

		return $this;
	}

	/**
	 * Method to get property Db
	 *
	 * @return  \Windwalker\Database\Driver\DatabaseDriver
	 */
	public function getDriver()
	{
		return $this->db;
	}

	/**
	 * Method to set property db
	 *
	 * @param   \Windwalker\Database\Driver\DatabaseDriver $db
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDriver($db)
	{
		$this->db = $db;

		return $this;
	}
}
