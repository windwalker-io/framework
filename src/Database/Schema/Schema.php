<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Database\Command\Schema;

/**
 * The Schema class.
 * 
 * @since  {DEPLOY_VERSION}
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
	 * Property keys.
	 *
	 * @var  array
	 */
	protected $keys = array();

	/**
	 * Property autoIncrement.
	 *
	 * @var  int
	 */
	protected $autoIncrement = null;

	/**
	 * Property ifNotExists.
	 *
	 * @var  boolean
	 */
	protected $ifNotExists = null;

	/**
	 * Property engine.
	 *
	 * @var  string
	 */
	protected $engine = null;

	/**
	 * Property defaultCharset.
	 *
	 * @var  string
	 */
	protected $defaultCharset = 'utf8';

	/**
	 * Class init.
	 *
	 * @param array   $columns
	 * @param array   $keys
	 * @param integer $autoIncrement
	 * @param string  $engine
	 * @param string  $defaultCharset
	 */
	public function __construct($columns = array(), $keys = array(), $autoIncrement = null, $engine = 'InnoDB', $defaultCharset = 'utf8')
	{
		$this->keys           = $keys;
		$this->engine         = $engine;
		$this->defaultCharset = $defaultCharset;
		$this->columns        = $columns;
		$this->autoIncrement  = $autoIncrement;
	}

	/**
	 * setColumn
	 *
	 * @param Column $column
	 *
	 * @return  static
	 */
	public function setColumn(Column $column)
	{
		$this->columns[$column->getName()] = $column;

		return $this;
	}

	/**
	 * getColumn
	 *
	 * @param string $name
	 *
	 * @return  Column
	 */
	public function getColumn($name)
	{
		if (empty($this->columns[$name]))
		{
			return null;
		}

		return $this->columns[$name];
	}

	/**
	 * Method to get property Columns
	 *
	 * @return  array
	 */
	public function getColumns()
	{
		return $this->columns;
	}

	/**
	 * Method to set property columns
	 *
	 * @param   array $columns
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setColumns($columns)
	{
		foreach ($columns as $column)
		{
			$this->setColumn($column);
		}

		return $this;
	}

	/**
	 * Method to get property Keys
	 *
	 * @return  array
	 */
	public function getKeys()
	{
		return $this->keys;
	}

	/**
	 * Method to set property keys
	 *
	 * @param   array $keys
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setKeys($keys)
	{
		$this->keys = $keys;

		return $this;
	}
}
