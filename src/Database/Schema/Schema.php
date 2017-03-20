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
 * @method  Column\Bit        bit($name)
 * @method  Column\Char       char($name)
 * @method  Column\Datetime   datetime($name)
 * @method  Column\Decimal    decimal($name)
 * @method  Column\Double     double($name)
 * @method  Column\FloatType  float($name)
 * @method  Column\Integer    integer($name)
 * @method  Column\Longtext   longtext($name)
 * @method  Column\Primary    primary($name)
 * @method  Column\Text       text($name)
 * @method  Column\Timestamp  timestamp($name)
 * @method  Column\Tinyint    tinyint($name)
 * @method  Column\Varchar    varchar($name)
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
	 * @param string        $name
	 * @param Column|string $column
	 *
	 * @return  Column
	 */
	public function add($name, $column)
	{
		$column->name($name);

		return $this->addColumn($column);
	}

	/**
	 * addColumn
	 *
	 * @param   Column|string $column
	 *
	 * @return  Column
	 */
	public function addColumn($column)
	{
		if (is_string($column) && class_exists($column))
		{
			$column = new $column;
		}

		if (!$column instanceof Column)
		{
			throw new \InvalidArgumentException(__METHOD__ . ' argument 1 need Column instance.');
		}

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
		$name = $key->getName();

		if (!$name)
		{
			$columns = (array) $key->getColumns();
			$name = 'idx_' . trim($this->table->getName(), '#_') . '_' . implode('_', $columns);

			$key->name($name);
		}

		$this->indexes[$key->getName()] = $key;

		return $key;
	}

	/**
	 * addIndex
	 *
	 * @param array|string  $columns
	 * @param string        $name
	 *
	 * @return Key
	 */
	public function addIndex($columns, $name = null)
	{
		return $this->addKey(new Key(Key::TYPE_INDEX, (array) $columns, $name));
	}

	/**
	 * addUniqueKey
	 *
	 * @param array  $columns
	 * @param string $name
	 *
	 * @return Key
	 */
	public function addUniqueKey($columns, $name = null)
	{
		return $this->addKey(new Key(Key::TYPE_UNIQUE, (array) $columns, $name));
	}

	/**
	 * addPrimaryKey
	 *
	 * @param array  $columns
	 *
	 * @return Key
	 */
	public function addPrimaryKey($columns)
	{
		return $this->addKey(new Key(Key::TYPE_PRIMARY, (array) $columns, null));
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
	 *
	 * @since   3.0
	 */
	public function getDateFormat()
	{
		return $this->getTable()->getDriver()->getQuery(true)->getDateFormat();
	}

	/**
	 * getNullDate
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function getNullDate()
	{
		return $this->getTable()->getDriver()->getQuery(true)->getNullDate();
	}
}
