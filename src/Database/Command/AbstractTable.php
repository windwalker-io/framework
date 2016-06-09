<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Command;

use Windwalker\Database\Driver\AbstractDatabaseDriver;
use Windwalker\Database\Driver\DatabaseAwareTrait;
use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\DataType;
use Windwalker\Database\Schema\Schema;

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
	protected $name = null;

	/**
	 * A cache to store Table columns.
	 *
	 * @var \stdClass[]
	 */
	protected $columnCache = array();

	/**
	 * Property indexesCache.
	 *
	 * @var  \stdClass[]
	 */
	protected $indexCache;

	/**
	 * Property database.
	 *
	 * @var  AbstractDatabase
	 */
	protected $database;

	/**
	 * Property driver.
	 *
	 * @var  \Windwalker\Database\Driver\AbstractDatabaseDriver
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param string         $table
	 * @param AbstractDatabaseDriver $db
	 */
	public function __construct($table, AbstractDatabaseDriver $db)
	{
		$this->name = $table;

		$this->db = $db;
	}

	/**
	 * create
	 *
	 * @param   callable|Schema $callback
	 * @param   bool            $ifNotExists
	 * @param   array           $options
	 *
	 * @return  static
	 */
	abstract public function create($callback, $ifNotExists = true, $options = array());

	/**
	 * update
	 *
	 * @param   callable|Schema  $schema
	 *
	 * @return  static
	 */
	public function update($schema)
	{
		$schema = $this->callSchema($schema);

		foreach ($schema->getColumns() as $column)
		{
			$this->addColumn($column);
		}

		foreach ($schema->getIndexes() as $index)
		{
			$this->addIndex($index);
		}

		return $this->reset();
	}

	/**
	 * save
	 *
	 * @param   callable|Schema  $schema
	 * @param   bool             $ifNotExists
	 * @param   array            $options
	 *
	 * @return  $this
	 */
	public function save($schema, $ifNotExists = true, $options = array())
	{
		$schema = $this->callSchema($schema);

		if ($this->exists())
		{
			$this->update($schema);
		}
		else
		{
			$this->create($schema, $ifNotExists, $options);
		}

		$database = $this->db->getDatabase();
		$database->reset();

		return $this->reset();
	}

	/**
	 * drop
	 *
	 * @param bool   $ifExists
	 * @param string $option
	 *
	 * @return  static
	 */
	public function drop($ifExists = true, $option = '')
	{
		$builder = $this->db->getQuery(true)->getGrammar();

		$query = $builder::dropTable($this->getName(), $ifExists, $option);

		$this->db->setQuery($query)->execute();

		return $this->reset();
	}

	/**
	 * exists
	 *
	 * @return  boolean
	 */
	public function exists()
	{
		$database = $this->db->getDatabase();

		return $database->tableExists($this->getName());
	}

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
	public function lock()
	{
		$this->db->setQuery('LOCK TABLES ' . $this->db->quoteName($this->getName()) . ' WRITE');

		return $this;
	}

	/**
	 * unlock
	 *
	 * @return  static  Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function unlock()
	{
		$this->db->setQuery('UNLOCK TABLES')->execute();

		return $this;
	}

	/**
	 * Method to truncate a table.
	 *
	 * @return  static
	 *
	 * @since   2.0
	 * @throws  \RuntimeException
	 */
	public function truncate()
	{
		$this->db->setQuery('TRUNCATE TABLE ' . $this->db->quoteName($this->getName()))->execute();

		return $this;
	}

	/**
	 * getDetail
	 *
	 * @return  array|boolean
	 */
	public function getDetail()
	{
		return $this->db->getDatabase()->getTableDetail($this->getName());
	}

	/**
	 * Get table columns.
	 *
	 * @param bool $refresh
	 *
	 * @return array Table columns with type.
	 */
	public function getColumns($refresh = false)
	{
		return array_keys($this->getColumnDetails($refresh));
	}

	/**
	 * getColumnDetails
	 *
	 * @param bool $refresh
	 *
	 * @return mixed
	 */
	abstract public function getColumnDetails($refresh = false);

	/**
	 * getColumnDetail
	 *
	 * @param   string  $column
	 *
	 * @return \stdClass
	 */
	public function getColumnDetail($column)
	{
		$columns = $this->getColumnDetails();

		return isset($columns[$column]) ? $columns[$column] : null;
	}

	/**
	 * hasColumn
	 *
	 * @param   string  $column
	 *
	 * @return  boolean
	 */
	public function hasColumn($column)
	{
		return (bool) $this->getColumnDetail($column);
	}

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
	public function dropColumn($name)
	{
		if (!$this->hasColumn($name))
		{
			return $this;
		}

		$builder = $this->db->getQuery(true)->getGrammar();

		$query = $builder::dropColumn($this->getName(), $name);

		$this->db->setQuery($query)->execute();

		return $this->reset();
	}

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
	 * @param array  $columns
	 * @param string $name
	 * @param string $comment
	 * @param array  $options
	 *
	 * @return static
	 */
	abstract public function addIndex($type, $columns = array(), $name = null, $comment = null, $options = array());

	/**
	 * dropIndex
	 *
	 * @param string  $name
	 *
	 * @return  static
	 */
	abstract public function dropIndex($name);

	/**
	 * getIndexes
	 *
	 * @return  array
	 */
	abstract public function getIndexes();

	/**
	 * hasIndex
	 *
	 * @param   string  $name
	 *
	 * @return  boolean
	 */
	public function hasIndex($name)
	{
		$indexes = $this->getIndexes();

		foreach ($indexes as $index)
		{
			if ($index->Key_name == $name)
			{
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Method to get property Table
	 *
	 * @return  string
	 */
	public function getName()
	{
		if ($this->database instanceof AbstractDatabase && $this->database->getName() != $this->db->getCurrentDatabase())
		{
			return $this->database->getName() . '.' . $this->name;
		}

		return $this->name;
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
		$this->name = $name;

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

	/**
	 * getSchema
	 *
	 * @return  Schema
	 */
	public function getSchema()
	{
		return new Schema($this);
	}

	/**
	 * callSchema
	 *
	 * @param   callable|Schema  $schema
	 *
	 * @return  Schema
	 */
	protected function callSchema($schema)
	{
		if (!$schema instanceof Schema && is_callable($schema))
		{
			$s = $this->getSchema();

			call_user_func($schema, $s);

			$schema = $s;
		}

		if (!$schema instanceof Schema)
		{
			throw new \InvalidArgumentException('Argument 1 should be Schema object.');
		}

		return $schema;
	}

	/**
	 * Method to get property Database
	 *
	 * @return  AbstractDatabase
	 */
	public function getDatabase()
	{
		return $this->database;
	}

	/**
	 * Method to set property database
	 *
	 * @param   AbstractDatabase $database
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDatabase($database)
	{
		if (is_string($database))
		{
			$database = $this->db->getDatabase($database);
		}

		$this->database = $database;

		return $this;
	}

	/**
	 * getTypeMapper
	 *
	 * @return  DataType
	 */
	public function getTypeMapper()
	{
		$driver = ucfirst($this->db->getName());

		return sprintf('Windwalker\Database\Driver\%s\%sType', $driver, $driver);
	}

	/**
	 * prepareColumn
	 *
	 * @param Column $column
	 *
	 * @return  Column
	 */
	protected function prepareColumn(Column $column)
	{
		$typeMapper = $this->getTypeMapper();

		$type   = $typeMapper::getType($column->getType());
		$length = $column->getLength() ? : $typeMapper::getLength($type);

		$length = $length ? '(' . $length . ')' : null;

		// Prepare default value
		$this->prepareDefaultValue($column);

		return $column->type($type)
			->length($length);
	}

	/**
	 * prepareDefaultValue
	 *
	 * @param Column $column
	 *
	 * @return  Column
	 */
	protected function prepareDefaultValue(Column $column)
	{
		$typeMapper = $this->getTypeMapper();

		$default = $column->getDefault();

		if (!$column->getAllowNull() && $default === null && !$column->isPrimary())
		{
			$default = $typeMapper::getDefaultValue($column->getType());

			$column->defaultValue($default);
		}

		return $column;
	}

	/**
	 * reset
	 *
	 * @return  static
	 */
	public function reset()
	{
		$this->columnCache = array();
		$this->indexCache = array();
		$this->database = null;

		return $this;
	}
}
