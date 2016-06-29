<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DataMapper;

use Windwalker\Data\Data;
use Windwalker\Data\DataSet;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\AbstractDatabaseDriver;
use Windwalker\Database\Query\QueryHelper;
use Windwalker\Query\Query;

/**
 * Relation Database Mapper.
 *
 * Provides join functions help ue select multiple tables.
 */
class RelationDataMapper extends DataMapper
{
	/**
	 * Property tables.
	 *
	 * @var  DataSet
	 */
	protected $tables = null;

	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = array();

	/**
	 * getInstance
	 *
	 * @param string                 $alias  Table alias.
	 * @param string                 $table  Table name.
	 * @param string|array           $pk     Primary key.
	 * @param AbstractDatabaseDriver $db     Database adapter.
	 *
	 * @return  static
	 */
	public static function newInstance($alias = null, $table = null, $pk = 'id', AbstractDatabaseDriver $db = null)
	{
		return new static($alias, $table, $pk, $db);
	}

	/**
	 * Constructor.
	 *
	 * @param string                 $alias  Table alias.
	 * @param string                 $table  Table name.
	 * @param string|array           $pk     Primary key.
	 * @param AbstractDatabaseDriver $db     Database adapter.
	 */
	public function __construct($alias, $table, $pk = 'id', AbstractDatabaseDriver $db = null)
	{
		$this->db = $db ? : DatabaseFactory::getDbo();

		$this->keys = $pk ? : $alias . '.' . $pk;

		$this->tables = new DataSet;

		if ($alias && $table)
		{
			$this->addTable($alias, $table);
		}

		$this->init();
	}

	/**
	 * getFindQuery
	 *
	 * @param   array   $conditions Where conditions, you can use array or Compare object.
	 * @param   array   $orders     Order sort, can ba string, array or object.
	 * @param   integer $start      Limit start number.
	 * @param   integer $limit      Limit rows.
	 *
	 * @return  \Windwalker\Query\Query
	 */
	protected function getFindQuery(array $conditions, array $orders, $start, $limit)
	{
		$query = $this->getQuery();
		
		$queryHelper = new QueryHelper($this->db);

		foreach ($this->tables as $tableData)
		{
			$queryHelper->addTable($tableData->alias, $tableData->table, $tableData->conditions, $tableData->joinType);
		}

		$queryHelper->registerQueryTables($query);

		if (!$query->get('select'))
		{
			$query->select($queryHelper->getSelectFields());
		}

		$this->query = $query;

		return parent::getFindQuery($conditions, $orders, $start, $limit);
	}

	/**
	 * Add a join table.
	 *
	 * @param string   $alias      Table alias.
	 * @param string   $table      Table name.
	 * @param mixed    $conditions Join conditions, can be string, array or Compare object.
	 *                             Example:
	 *                             - `a.id = b.catid` => 'ON a.id = b.catid'
	 *                             - `array('a.lft <= b.lft', 'a.rgt >= b.rgt')` => 'ON a.lft <= b.lft AND a.rgt >= b.rgt'
	 *                             - `new EqCompare('a.id', 'b.catid')` => 'ON a.id = b.catid'
	 * @param string   $joinType   Which join type we use for this table, default is LEFT.
	 * @param boolean  $prefix     Select field add prefix.
	 *
	 * @return  RelationDataMapper Return self to support chaining.
	 */
	public function addTable($alias, $table, $conditions = null, $joinType = 'LEFT', $prefix = null)
	{
		$this->tables[$alias] = new Data(
			array(
				'alias' => $alias,
				'table' => $table,
				'from'  => $alias . '.' . $table,
				'conditions' => $conditions,
				'joinType' => $joinType,
				'prefix' => $prefix
			)
		);

		return $this;
	}

	/**
	 * Remove a join table.
	 *
	 * @param string $alias Using alias to remove.
	 *
	 * @return RelationDataMapper Return self to support chaining.
	 */
	public function removeTable($alias)
	{
		unset($this->tables[$alias]);

		return $this;
	}

	/**
	 * set
	 *
	 * @param   string  $name
	 * @param   mixed   $value
	 *
	 * @return  static
	 */
	public function set($name, $value)
	{
		$this->options[$name] = $value;

		return $this;
	}

	/**
	 * get
	 *
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return  mixed
	 */
	public function get($name, $default = null)
	{
		if (!isset($this->options[$name]))
		{
			return $default;
		}

		return $this->options[$name];
	}
}
