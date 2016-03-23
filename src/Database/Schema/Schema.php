<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Command\Schema;

use Windwalker\Database\Command\AbstractTable;
use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\Key;

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
	public function addColumn($name, Column $column)
	{
		$column->name($name);

		$this->table->addColumn($column);

		return $column;
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
		// Old style B/C
		if (in_array($name, array(Key::TYPE_INDEX, Key::TYPE_PRIMARY, Key::TYPE_UNIQUE)))
		{
			$ref = new \ReflectionClass('Windwalker\Database\Schema\Key');

			$this->getTable()->addIndex($key = $ref->newInstanceArgs(func_get_args()));

			return $key;
		}

		$this->addIndex($key = new Key(Key::TYPE_INDEX, $name, $columns));

		return $key;
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
		return $this->getTable()->addIndex(Key::TYPE_UNIQUE, $name, $columns);
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
		return $this->getTable()->addIndex(Key::TYPE_PRIMARY, $name, $columns);
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

		if (class_exists($class))
		{
			$column = array_shift($arguments);

			return $this->addColumn($column, new $class);
		}

		throw new \BadMethodCallException(sprintf('DataType or index: %s not exists.', $name));
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
}
