<?php
/**
 * Part of datamapper project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\DataMapper;

use Joomla\Database\DatabaseDriver;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\QueryHelper;

/**
 * Relation Database Mapper.
 *
 * Provides join functions help ue select multiple tables.
 */
class RelationDataMapper extends DataMapper
{
	/**
	 * The mappers.
	 *
	 * @var  array
	 */
	protected $mappers = array();

	/**
	 * Select columns.
	 *
	 * @var  array
	 */
	protected $select = array();

	/**
	 * Select type.
	 *
	 * @var  int
	 */
	protected $selectType = null;

	/**
	 * Constructor.
	 *
	 * @param string         $alias       Table alias.
	 * @param string         $table       Table name.
	 * @param string|array   $pk          Primary key.
	 * @param DatabaseDriver $db          Database adapter.
	 * @param QueryHelper    $queryHelper Query helper object.
	 */
	public function __construct($alias, $table, $pk = 'id', DatabaseDriver $db = null, QueryHelper $queryHelper = null)
	{
		$this->db = $db ? : DatabaseFactory::getDbo();

		$this->pk = $pk ? : $alias . '.' . $pk;

		$this->queryHelper = $queryHelper ? : new QueryHelper($this->db);

		$this->addTable($alias, $table);

		$this->configure();
	}

	/**
	 * Add a join table.
	 *
	 * @param string $alias      Table alias.
	 * @param string $table      Table name.
	 * @param mixed  $conditions Join conditions, can be string, array or Compare object.
	 *                           Example:
	 *                           - `a.id = b.catid` => 'ON a.id = b.catid'
	 *                           - `array('a.lft <= b.lft', 'a.rgt >= b.rgt')` => 'ON a.lft <= b.lft AND a.rgt >= b.rgt'
	 *                           - `new EqCompare('a.id', 'b.catid')` => 'ON a.id = b.catid'
	 * @param string $joinType   Which join type we use for this table, default is LEFT.
	 *
	 * @return  RelationDataMapper Return self to support chaining.
	 */
	public function addTable($alias, $table, $conditions = null, $joinType = 'LEFT')
	{
		$this->mappers[$alias] = ($table instanceof DataMapper) ? $table : new DataMapper($table);

		$this->queryHelper->addTable($alias, $table, $conditions, $joinType);

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
		$this->queryHelper->removeTable($alias);

		return $this;
	}

	/**
	 * Get select columns.
	 *
	 * @return array|string Select columns.
	 */
	public function getSelect()
	{
		return $this->select;
	}

	/**
	 * Set select columns.
	 *
	 * @param array|string $select Select columns.
	 *
	 * @return  RelationDataMapper  Return self to support chaining.
	 */
	public function setSelect($select)
	{
		$this->select = $select;

		return $this;
	}

	/**
	 * Get select type.
	 *
	 * @return int Select type.
	 */
	public function getSelectType()
	{
		return $this->selectType;
	}

	/**
	 * Set select type.
	 *
	 * @param int $selectType Select type: `QueryHelper::COLS_WITH_FIRST` or `QueryHelper::COLS_PREFIX_WITH_FIRST`.
	 *
	 *                        - COLS_WITH_FIRST        => Means first table use `alias`.`field` AS `field`
	 *                        - COLS_PREFIX_WITH_FIRST => Means first use  `alias`.`field` AS `alias_field`
	 *
	 *                        You can use `QueryHelper::COLS_WITH_FIRST | QueryHelper::COLS_PREFIX_WITH_FIRST` to enable both.
	 *
	 * @return RelationDataMapper  Return self to support chaining.
	 */
	public function setSelectType($selectType)
	{
		$this->selectType = $selectType;

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
		$query = $this->db->getQuery(true);

		// Conditions.
		$query = QueryHelper::buildWheres($query, $conditions);

		// Loop ordering
		foreach ($orders as $order)
		{
			$query->order($order);
		}

		// Build query
		$selectType = $this->selectType ? : QueryHelper::COLS_WITH_FIRST;

		$query->select($this->select ? : $this->queryHelper->getSelectFields($selectType));

		$this->queryHelper->registerQueryTables($query);

		return $this->db->setQuery($query, $start, $limit)->loadObjectList();
	}
}
