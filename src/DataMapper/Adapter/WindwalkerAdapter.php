<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\DataMapper\Adapter;

use Joomla\Database\DatabaseDriver;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\QueryHelper;
use Windwalker\DataMapper\Entity\Entity;

/**
 * Class JoomlaDatabaseAdapter
 *
 * @since 1.0
 */
class WindwalkerAdapter extends DatabaseAdapter
{
	/**
	 * Query helper.
	 *
	 * @var  QueryHelper
	 */
	protected $queryHelper = null;

	/**
	 * Constructor.
	 *
	 * @param DatabaseDriver $db          Database adapter.
	 * @param QueryHelper    $queryHelper Query helper object.
	 */
	public function __construct(DatabaseDriver $db = null, QueryHelper $queryHelper = null)
	{
		$this->db = $db ? : DatabaseFactory::getDbo();

		$this->queryHelper = $queryHelper ? : new QueryHelper($this->db);
	}

	/**
	 * Do find action.
	 *
	 * @param string  $table      The table name.
	 * @param array   $conditions Where conditions, you can use array or Compare object.
	 * @param array   $orders     Order sort, can ba string, array or object.
	 * @param integer $start      Limit start number.
	 * @param integer $limit      Limit rows.
	 *
	 * @return  mixed Found rows data set.
	 */
	public function find($table, array $conditions = array(), array $orders = array(), $start = 0, $limit = null)
	{
		$query = $this->db->getQuery(true);

		// Conditions.
		QueryHelper::buildWheres($query, $conditions);

		// Loop ordering
		foreach ($orders as $order)
		{
			$query->order($order);
		}

		// Build query
		$query->select('*')
			->from($table);

		return $this->db->setQuery($query, $start, $limit)->loadObjectList();
	}

	/**
	 * Do create action.
	 *
	 * @param string  $table The table name.
	 * @param mixed   $data  The data set contains data we want to store.
	 * @param string  $pk    The primary key column name.
	 *
	 * @return  mixed  Data set data with inserted id.
	 */
	public function create($table, $data, $pk = null)
	{
		return $this->db->insertObject($table, $data, $pk);
	}

	/**
	 * Do update action.
	 *
	 * @param string  $table      The table name.
	 * @param mixed   $data       Data set contain data we want to update.
	 * @param array   $condFields The where condition tell us record exists or not, if not set,
	 *                            will use primary key instead.
	 *
	 * @throws \Exception
	 * @return  mixed Updated data set.
	 */
	public function updateOne($table, $data, array $condFields = array())
	{
		return $this->db->updateObject($table, $data, $condFields = array());
	}

	/**
	 * Do updateAll action.
	 *
	 * @param string  $table      The table name.
	 * @param mixed   $data       The data we want to update to every rows.
	 * @param mixed   $conditions Where conditions, you can use array or Compare object.
	 *
	 * @throws \Exception
	 * @return  mixed Updated data set.
	 */
	public function updateAll($table, $data, array $conditions = array())
	{
		$command = DatabaseFactory::getCommand();

		return (boolean) $command->updateBatch($table, $data, $conditions);
	}

	/**
	 * Do delete action, this method should be override by sub class.
	 *
	 * @param string  $table      The table name.
	 * @param mixed   $conditions Where conditions, you can use array or Compare object.
	 *
	 * @throws \Exception
	 * @return  boolean Will be always true.
	 */
	public function delete($table, array $conditions = array())
	{
		$query = $this->db->getQuery(true);

		// Conditions.
		QueryHelper::buildWheres($query, $conditions);

		$query->delete($table);

		return $this->db->setQuery($query)->execute();
	}

	/**
	 * Get table fields.
	 *
	 * @param string $table Table name.
	 *
	 * @return  array
	 */
	public function getFields($table)
	{
		return array_keys(DatabaseFactory::getCommand()->getColumns($table));
	}

	/**
	 * transactionStart
	 *
	 * @param bool $asSavePoint
	 *
	 * @return  $this
	 */
	public function transactionStart($asSavePoint = false)
	{
		$this->db->transactionStart($asSavePoint);

		return $this;
	}

	/**
	 * transactionCommit
	 *
	 * @param bool $asSavePoint
	 *
	 * @return  $this
	 */
	public function transactionCommit($asSavePoint = false)
	{
		$this->db->transactionCommit($asSavePoint);

		return $this;
	}

	/**
	 * transactionRollback
	 *
	 * @param bool $asSavePoint
	 *
	 * @return  $this
	 */
	public function transactionRollback($asSavePoint = false)
	{
		$this->db->transactionRollback($asSavePoint);

		return $this;
	}
}

