<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\DataMapper;

use Windwalker\Data\Data;
use Windwalker\Data\DataSet;
use Windwalker\DataMapper\Adapter\DatabaseAdapter;
use Windwalker\DataMapper\Adapter\DatabaseAdapterInterface;

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
	 * Constructor.
	 *
	 * @param string                   $alias       Table alias.
	 * @param string                   $table       Table name.
	 * @param string|array             $pk          Primary key.
	 * @param DatabaseAdapterInterface $db          Database adapter.
	 */
	public function __construct($alias, $table, $pk = 'id', DatabaseAdapterInterface $db = null)
	{
		$this->db = $db ? : DatabaseAdapter::getInstance();

		$this->pk = $pk ? : $alias . '.' . $pk;

		$this->tables = new DataSet;

		$this->addTable($alias, $table);

		$this->prepare();
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
	 * Do find action.
	 *
	 * @param array   $conditions Where conditions, you can use array or Compare object.
	 * @param array   $orders     Order sort, can ba string, array or object.
	 * @param integer $start      Limit start number.
	 * @param integer $limit      Limit rows.
	 *
	 * @return  mixed Found rows data set.
	 */
	protected function doFind(array $conditions, array $orders, $start, $limit)
	{
		return $this->db->find($this->tables, $this->selectFields, $conditions, $orders, $start, $limit);
	}
}
