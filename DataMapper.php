<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\DataMapper;

use Joomla\Database\DatabaseDriver;
use Windwalker\DataMapper\Database\DatabaseFactory;
use Windwalker\DataMapper\Database\QueryHelper;
use Windwalker\DataMapper\Entity\Entity;

/**
 * Class DataMapper
 */
class DataMapper extends AbstractDataMapper
{
	/**
	 * Property db.
	 *
	 * @var DatabaseDriver
	 */
	protected $db;

	/**
	 * Property queryHelper.
	 *
	 * @var  QueryHelper
	 */
	protected $queryHelper = null;

	/**
	 * Constructor
	 *
	 * @param null           $table
	 * @param string         $pk
	 * @param DatabaseDriver $db
	 */
	public function __construct($table = null, $pk = 'id', DatabaseDriver $db = null, QueryHelper $queryHelper = null)
	{
		$this->db = $db ? : DatabaseFactory::getDbo();

		$this->queryHelper = $queryHelper ? : new QueryHelper($this->db);

		parent::__construct($table, $pk);
	}

	/**
	 * doFind
	 *
	 * @param array $conditions
	 * @param array $orders
	 * @param int   $start
	 * @param int   $limit
	 *
	 * @return  mixed|void
	 */
	protected function doFind(array $conditions, array $orders, $start, $limit)
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
			->from($this->table);

		return $this->db->setQuery($query, $start, $limit)->loadObjectList();
	}

	/**
	 * doCreate
	 *
	 * @param $dataset
	 *
	 * @throws \Exception
	 * @return  mixed
	 */
	protected function doCreate($dataset)
	{
		$this->db->transactionStart(true);

		try
		{
			foreach ($dataset as &$data)
			{
				$entity = new Entity($this->getFields($this->table), $data);

				$pk = $this->getPrimaryKey();

				$this->db->insertObject($this->table, $entity, $pk);

				$data->$pk = $entity->$pk;
			}
		}
		catch (\Exception $e)
		{
			$this->db->transactionRollback(true);

			throw $e;
		}

		$this->db->transactionCommit(true);

		return $dataset;
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
		$this->db->transactionStart(true);

		try
		{
			foreach ($dataset as &$data)
			{
				$entity = new Entity($this->getFields($this->table), $data);

				$this->db->updateObject($this->table, $entity, $this->getPrimaryKey());
			}
		}
		catch (\Exception $e)
		{
			$this->db->transactionRollback(true);

			throw $e;
		}

		$this->db->transactionCommit(true);

		return $dataset;
	}

	/**
	 * doUpdateAll
	 *
	 * @param $data
	 * @param $conditions
	 *
	 * @throws \Exception
	 * @return  mixed
	 */
	protected function doUpdateAll($data, $conditions)
	{
		$this->db->transactionStart(true);

		$command = DatabaseFactory::getCommand();

		try
		{
			$result = (boolean) $command->updateBatch($this->table, $data, $conditions);
		}
		catch (\Exception $e)
		{
			$this->db->transactionRollback(true);

			throw $e;
		}

		$this->db->transactionCommit(true);

		return $result;
	}

	/**
	 * doDelete
	 *
	 * @param array $conditions
	 *
	 * @throws \Exception
	 * @return  mixed
	 */
	protected function doDelete($conditions)
	{
		$query = $this->db->getQuery(true);

		// Conditions.
		QueryHelper::buildWheres($query, $conditions);

		$query->delete($this->table);

		$this->db->transactionStart(true);

		try
		{
			$result = (boolean) $this->db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			$this->db->transactionRollback(true);

			throw $e;
		}

		$this->db->transactionCommit(true);

		return $result;
	}

	/**
	 * getDb
	 *
	 * @return  \Joomla\Database\DatabaseDriver
	 */
	public function getDb()
	{
		return $this->db;
	}

	/**
	 * setDb
	 *
	 * @param   \Joomla\Database\DatabaseDriver $db
	 *
	 * @return  DataMapper  Return self to support chaining.
	 */
	public function setDb($db)
	{
		$this->db = $db;

		return $this;
	}

	/**
	 * getFields
	 *
	 * @param $table
	 *
	 * @return  array
	 */
	protected function getFields($table = null)
	{
		$table = $table ? : $this->table;

		return array_keys(DatabaseFactory::getCommand()->getColumns($table));
	}
}
