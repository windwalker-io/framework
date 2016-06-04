<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Schema;

use Windwalker\Database\Command\AbstractTable;
use Windwalker\Database\Schema\Column;

/**
 * The Schema class.
 *
 * @method  Column  bit($name)
 * @method  Column  char($name)
 * @method  Column  datetime($name)
 * @method  Column  decimal($name)
 * @method  Column  double($name)
 * @method  Column  float($name)
 * @method  Column  integer($name)
 * @method  Column  logintext($name)
 * @method  Column  primary($name)
 * @method  Column  text($name)
 * @method  Column  timestamp($name)
 * @method  Column  tinyint($name)
 * @method  Column  varchar($name)
 *
 * @since  2.1.8
 */
class Schema
{
	/**
	 * Property columns.
	 *
	 * @var  Column[]
	 */
	protected $columns = array();

	/**
	 * Property indexes.
	 *
	 * @var  Key[]
	 */
	protected $indexes = array();

	/**
	 * Property table.
	 *
	 * @var  AbstractTable
	 */
	protected $table;

	/**
	 * Schema constructor.
	 *
	 * @param AbstractTable $table
	 */
	public function __construct(AbstractTable $table)
	{
		$this->table = $table;
	}

	/**
	 * addColumn
	 *
	 * @param string $name
	 * @param Column $column
	 *
	 * @return  Column
	 */
	public function add($name, Column $column)
	{
		$column->name($name);

		return $this->addColumn($column);
	}

	/**
	 * addColumn
	 *
	 * @param   Column $column
	 *
	 * @return  Column
	 */
	public function addColumn(Column $column)
	{
		$this->columns[$column->getName()] = $column;

		return $column;
	}

	/**
	 * addKey
	 *
	 * @param Key $key
	 *
	 * @return  Key
	 */
	public function addKey(Key $key)
	{
		$this->indexes[$key->getName()] = $key;

		return $key;
	}

	/**
	 * addIndex
	 *
	 * @param string $name
	 * @param array  $columns
	 *
	 * @return  Key
	 */
	public function addIndex($name = null, $columns = null)
	{
		return $this->addKey(new Key(Key::TYPE_INDEX, $name, $columns));
	}

	/**
	 * addUniqueKey
	 *
	 * @param string $name
	 * @param array  $columns
	 *
	 * @return  Key
	 */
	public function addUniqueKey($name, $columns = array())
	{
		return $this->addKey(new Key(Key::TYPE_UNIQUE, $name, $columns));
	}

	/**
	 * addPrimaryKey
	 *
	 * @param string $name
	 * @param array  $columns
	 *
	 * @return  Key
	 */
	public function addPrimaryKey($name, $columns)
	{
		return $this->addKey(new Key(Key::TYPE_PRIMARY, $name, $columns));
	}

	/**
	 * is triggered when invoking inaccessible methods in an object context.
	 *
	 * @param $name      string
	 * @param $arguments array
	 *
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		$class = 'Windwalker\Database\Schema\Column\\' . ucfirst($name);

		if (!class_exists($class))
		{
			$class = 'Windwalker\Database\Schema\Column\\' . ucfirst($name) . 'Type';
		}

		if (!class_exists($class))
		{
			throw new \BadMethodCallException(sprintf('DataType or index: %s not exists.', $name));
		}

		$column = array_shift($arguments);

		return $this->add($column, new $class);
	}

	/**
	 * Method to get property Table
	 *
	 * @return  AbstractTable
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Method to set property table
	 *
	 * @param   AbstractTable $table
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setTable($table)
	{
		$this->table = $table;

		return $this;
	}

	/**
	 * Method to get property Columns
	 *
	 * @return  Column[]
	 */
	public function getColumns()
	{
		return $this->columns;
	}

	/**
	 * Method to set property columns
	 *
	 * @param   Column[] $columns
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setColumns($columns)
	{
		$this->columns = $columns;

		return $this;
	}

	/**
	 * Method to get property Indexes
	 *
	 * @return  Key[]
	 */
	public function getIndexes()
	{
		return $this->indexes;
	}

	/**
	 * Method to set property indexes
	 *
	 * @param   Key[] $indexes
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setIndexes($indexes)
	{
		$this->indexes = $indexes;

		return $this;
	}

	/**
	 * getDateFormat
	 *
	 * @return  string
	 */
	public function getDateFormat()
	{
		return $this->getTable()->getDriver()->getQuery(true)->getDateFormat();
	}

	/**
	 * getNullDate
	 *
	 * @return  string
	 */
	public function getNullDate()
	{
		return $this->getTable()->getDriver()->getQuery(true)->getNullDate();
	}
}
