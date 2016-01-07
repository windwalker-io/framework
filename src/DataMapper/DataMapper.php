<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DataMapper;

use Windwalker\DataMapper\Adapter\AbstractDatabaseAdapter;
use Windwalker\DataMapper\Adapter\DatabaseAdapterInterface;
use Windwalker\DataMapper\Entity\Entity;

/**
 * Main Database Mapper class.
 */
class DataMapper extends AbstractDataMapper
{
	/**
	 * The DB adapter.
	 *
	 * @var AbstractDatabaseAdapter
	 */
	protected $db = null;

	/**
	 * Constructor.
	 *
	 * @param   string                    $table  Table name.
	 * @param   string|array              $pk     Primary key.
	 * @param   DatabaseAdapterInterface  $db     Database adapter.
	 */
	public function __construct($table = null, $pk = 'id', DatabaseAdapterInterface $db = null)
	{
		$this->db = $db ? : AbstractDatabaseAdapter::getInstance();

		parent::__construct($table, $pk);
	}

	/**
	 * Do find action.
	 *
	 * @param   array    $conditions  Where conditions, you can use array or Compare object.
	 * @param   array    $orders      Order sort, can ba string, array or object.
	 * @param   integer  $start       Limit start number.
	 * @param   integer  $limit       Limit rows.
	 *
	 * @return  mixed  Found rows data set.
	 */
	protected function doFind(array $conditions, array $orders, $start, $limit)
	{
		return $this->db->find($this->table, $this->selectFields, $conditions, $orders, $start, $limit);
	}

	/**
	 * Do create action.
	 *
	 * @param mixed $dataset The data set contains data we want to store.
	 *
	 * @throws \Exception
	 * @return  mixed  Data set data with inserted id.
	 */
	protected function doCreate($dataset)
	{
		!$this->useTransaction ? : $this->db->transactionStart(true);

		try
		{
			foreach ($dataset as $k => $data)
			{
				if (!($data instanceof $this->dataClass))
				{
					$data = $this->bindData($data);
				}

				$entity = new Entity($this->getFields($this->table), $data);

				$pk = $this->getPrimaryKey();

				$this->db->create($this->table, $entity, $pk);

				$data->$pk = $entity->$pk;

				$dataset[$k] = $data;
			}
		}
		catch (\Exception $e)
		{
			!$this->useTransaction ? : $this->db->transactionRollback(true);

			throw $e;
		}

		!$this->useTransaction ? : $this->db->transactionCommit(true);

		return $dataset;
	}

	/**
	 * Do update action.
	 *
	 * @param   mixed  $dataset     Data set contain data we want to update.
	 * @param   array  $condFields  The where condition tell us record exists or not, if not set,
	 *                              will use primary key instead.
	 * @param   bool  $updateNulls  Update empty fields or not.
	 *
	 * @throws  \Exception
	 * @return  mixed Updated data set.
	 */
	protected function doUpdate($dataset, array $condFields, $updateNulls = false)
	{
		!$this->useTransaction ? : $this->db->transactionStart(true);

		try
		{
			foreach ($dataset as $k => $data)
			{
				if (!($data instanceof $this->dataClass))
				{
					$data = $this->bindData($data);
				}

				$entity = new Entity($this->getFields($this->table), $data);

				$this->db->updateOne($this->table, $entity, $condFields, $updateNulls);

				$dataset[$k] = $data;
			}
		}
		catch (\Exception $e)
		{
			!$this->useTransaction ? : $this->db->transactionRollback(true);

			throw $e;
		}

		!$this->useTransaction ? : $this->db->transactionCommit(true);

		return $dataset;
	}

	/**
	 * Do updateAll action.
	 *
	 * @param   mixed  $data        The data we want to update to every rows.
	 * @param   mixed  $conditions  Where conditions, you can use array or Compare object.
	 *
	 * @throws  \Exception
	 * @return  boolean
	 */
	protected function doUpdateAll($data, array $conditions)
	{
		!$this->useTransaction ? : $this->db->transactionStart(true);

		try
		{
			$result = $this->db->updateAll($this->table, $data, $conditions);
		}
		catch (\Exception $e)
		{
			!$this->useTransaction ? : $this->db->transactionRollback(true);

			throw $e;
		}

		!$this->useTransaction ? : $this->db->transactionCommit(true);

		return $result;
	}

	/**
	 * Do flush action, this method should be override by sub class.
	 *
	 * @param mixed $dataset    Data set contain data we want to update.
	 * @param mixed $conditions Where conditions, you can use array or Compare object.
	 *
	 * @throws \Exception
	 * @return  mixed Updated data set.
	 */
	protected function doFlush($dataset, array $conditions)
	{
		!$this->useTransaction ? : $this->db->transactionStart(true);

		try
		{
			if (!$this->delete($conditions))
			{
				throw new \Exception(sprintf('Delete row fail when updating relations table: %s', $this->table));
			}

			if (!$this->create($dataset))
			{
				throw new \Exception(sprintf('Insert row fail when updating relations table: %s', $this->table));
			}
		}
		catch (\Exception $e)
		{
			!$this->useTransaction ? : $this->db->transactionRollback(true);

			throw $e;
		}

		!$this->useTransaction ? : $this->db->transactionCommit(true);

		return $dataset;
	}

	/**
	 * Do delete action, this method should be override by sub class.
	 *
	 * @param mixed $conditions Where conditions, you can use array or Compare object.
	 *
	 * @throws \Exception
	 * @return  boolean Will be always true.
	 */
	protected function doDelete(array $conditions)
	{
		!$this->useTransaction ? : $this->db->transactionStart(true);

		try
		{
			$result = $this->db->delete($this->table, $conditions);
		}
		catch (\Exception $e)
		{
			!$this->useTransaction ? : $this->db->transactionRollback(true);

			throw $e;
		}

		!$this->useTransaction ? : $this->db->transactionCommit(true);

		return $result;
	}

	/**
	 * Get DB adapter.
	 *
	 * @return  \Windwalker\Database\Driver\AbstractDatabaseDriver Db adapter.
	 */
	public function getDb()
	{
		return $this->db;
	}

	/**
	 * Set db adapter.
	 *
	 * @param   \Windwalker\Database\Driver\AbstractDatabaseDriver $db Db adapter.
	 *
	 * @return  DataMapper  Return self to support chaining.
	 */
	public function setDb($db)
	{
		$this->db = $db;

		return $this;
	}

	/**
	 * Get table fields.
	 *
	 * @param string $table Table name.
	 *
	 * @return  array
	 */
	protected function getFields($table = null)
	{
		$table = $table ? : $this->table;

		return $this->db->getFields($table);
	}
}
