<?php
/**
 * Part of datamapper project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\DataMapper;

use Joomla\Database\DatabaseDriver;
use Joomla\Utilities\ArrayHelper;
use Windwalker\DataMapper\Database\DatabaseFactory;
use Windwalker\DataMapper\Database\QueryHelper;

/**
 * Class RelationDataMapper
 *
 * @since 1.0
 */
class RelationDataMapper extends DataMapper
{
	/**
	 * Property mappers.
	 *
	 * @var  array
	 */
	protected $mappers = array();

	/**
	 * Property select.
	 *
	 * @var  array
	 */
	protected $select = array();

	/**
	 * Property selectType.
	 *
	 * @var  int
	 */
	protected $selectType = null;

	/**
	 * Constructor.
	 *
	 * @param string         $alias
	 * @param mixed          $table
	 * @param string         $pk
	 * @param DatabaseDriver $db
	 * @param QueryHelper    $queryHelper
	 */
	public function __construct($alias = null, $table = null, $pk = 'id', DatabaseDriver $db = null, QueryHelper $queryHelper = null)
	{
		$this->db = $db ? : DatabaseFactory::getDbo();

		$this->pk = $pk;

		$this->queryHelper = $queryHelper ? : new QueryHelper($this->db);

		if ($table)
		{
			$this->addTable($alias, $table);
		}
	}

	/**
	 * addTable
	 *
	 * @param string $alias
	 * @param string $table
	 * @param mixed  $conditions
	 * @param string $joinType
	 *
	 * @return  RelationDataMapper
	 */
	public function addTable($alias, $table, $conditions = null, $joinType = 'LEFT')
	{
		$this->mappers[$alias] = ($table instanceof DataMapper) ? $table : new DataMapper($table);

		$this->queryHelper->addTable($alias, $table, $conditions, $joinType);

		return $this;
	}

	/**
	 * removeTable
	 *
	 * @param string $alias
	 *
	 * @return  $this
	 */
	public function removeTable($alias)
	{
		$this->queryHelper->removeTable($alias);

		return $this;
	}

	/**
	 * getSelect
	 *
	 * @return  array
	 */
	public function getSelect()
	{
		return $this->select;
	}

	/**
	 * setSelect
	 *
	 * @param   array $select
	 *
	 * @return  RelationDataMapper  Return self to support chaining.
	 */
	public function setSelect($select)
	{
		$this->select = $select;

		return $this;
	}

	/**
	 * getSelectType
	 *
	 * @return  int
	 */
	public function getSelectType()
	{
		return $this->selectType;
	}

	/**
	 * setSelectType
	 *
	 * @param   int $selectType
	 *
	 * @return  RelationDataMapper  Return self to support chaining.
	 */
	public function setSelectType($selectType)
	{
		$this->selectType = $selectType;

		return $this;
	}

	/**
	 * doFind
	 *
	 * @param $conditions
	 * @param $order
	 * @param $start
	 * @param $limit
	 *
	 * @return  mixed
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

	/**
	 * doUpdate
	 *
	 * @param $dataset
	 * @param $conditions
	 *
	 * @return  mixed
	 */
	protected function doUpdate($dataset)
	{
		throw new \LogicException(__CLASS__ . ' do not support ' . __METHOD__ . '().');
	}

	/**
	 * doUpdateAll
	 *
	 * @param $data
	 * @param $conditions
	 *
	 * @throws \LogicException
	 * @return  mixed
	 */
	protected function doUpdateAll($data, $conditions)
	{
		throw new \LogicException(__CLASS__ . ' do not support ' . __METHOD__ . '().');
	}

	/**
	 * doDelete
	 *
	 * @param $conditions
	 *
	 * @return  mixed
	 */
	protected function doDelete($conditions)
	{
		throw new \LogicException(__CLASS__ . ' do not support ' . __METHOD__ . '().');
	}
}
