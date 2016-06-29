<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DataMapper;

use Windwalker\Data\DataSet;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\AbstractDatabaseDriver;
use Windwalker\Database\Query\QueryHelper;
use Windwalker\DataMapper\Entity\Entity;

/**
 * Main Database Mapper class.
 */
class DataMapper extends AbstractDataMapper
{
	/**
	 * The DB adapter.
	 *
	 * @var AbstractDatabaseDriver
	 */
	protected $db = null;

	/**
	 * Constructor.
	 *
	 * @param   string                 $table Table name.
	 * @param   string|array           $keys  Primary key.
	 * @param   AbstractDatabaseDriver $db    Database adapter.
	 */
	public function __construct($table = null, $keys = 'id', AbstractDatabaseDriver $db = null)
	{
		$this->db = $db ? : DatabaseFactory::getDbo();

		parent::__construct($table, $keys);
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
		$query = $this->db->getQuery(true);

		// Conditions.
		QueryHelper::buildWheres($query, $conditions);

		// Loop ordering
		foreach ($orders as $order)
		{
			$query->order($order);
		}

		$query->from($this->table);

		// Build query
		$query->select($this->selectFields ? : '*')
			->limit($limit, $start);

		if (isset($options['group']))
		{
			$query->group($options['group']);
		}

		if (isset($options['having']))
		{
			$query->having($options['having']);
		}

		return $this->db->setQuery($query)->loadAll();
	}

	/**
	 * Do create action.
	 *
	 * @param  mixed $dataset The data set contains data we want to store.
	 *
	 * @return mixed
	 * 
	 * @throws \Exception
	 * @throws \Throwable
	 */
	protected function doCreate($dataset)
	{
		!$this->useTransaction ? : $this->db->getTransaction(true)->start();

		try
		{
			foreach ($dataset as $k => $data)
			{
				if (!($data instanceof $this->dataClass))
				{
					$data = $this->bindData($data);
				}

				$entity = new Entity($this->getFields($this->table), $data);

				$entity = $this->prepareDefaultValue($entity);

				$pk = $this->getKeyName();

				$this->db->getWriter()->insertOne($this->table, $entity, $pk);

				$data->$pk = $entity->$pk;

				$dataset[$k] = $data;
			}
		}
		catch (\Exception $e)
		{
			!$this->useTransaction ? : $this->db->getTransaction(true)->rollback();

			throw $e;
		}
		catch (\Throwable $e)
		{
			!$this->useTransaction ? : $this->db->getTransaction(true)->rollback();

			throw $e;
		}

		!$this->useTransaction ? : $this->db->getTransaction(true)->commit();

		return $dataset;
	}

	/**
	 * Do update action.
	 *
	 * @param   mixed $dataset      Data set contain data we want to update.
	 * @param   array $condFields   The where condition tell us record exists or not, if not set,
	 *                              will use primary key instead.
	 * @param   bool  $updateNulls  Update empty fields or not.
	 *
	 * @return  mixed
	 *
	 * @throws \Exception
	 * @throws \Throwable
	 */
	protected function doUpdate($dataset, array $condFields, $updateNulls = false)
	{
		!$this->useTransaction ? : $this->db->getTransaction(true)->start();

		try
		{
			foreach ($dataset as $k => $data)
			{
				if (!($data instanceof $this->dataClass))
				{
					$data = $this->bindData($data);
				}

				$entity = new Entity($this->getFields($this->table), $data);

				if ($updateNulls)
				{
					$entity = $this->prepareDefaultValue($entity);
				}

				$this->db->getWriter()->updateOne($this->table, $entity, $condFields, $updateNulls);

				$dataset[$k] = $data;
			}
		}
		catch (\Exception $e)
		{
			!$this->useTransaction ? : $this->db->getTransaction(true)->rollback();

			throw $e;
		}
		catch (\Throwable $e)
		{
			!$this->useTransaction ? : $this->db->getTransaction(true)->rollback();

			throw $e;
		}

		!$this->useTransaction ? : $this->db->getTransaction(true)->commit();

		return $dataset;
	}

	/**
	 * Do updateAll action.
	 *
	 * @param   mixed $data       The data we want to update to every rows.
	 * @param   mixed $conditions Where conditions, you can use array or Compare object.
	 *
	 * @return  boolean
	 *
	 * @throws \Exception
	 * @throws \Throwable
	 */
	protected function doUpdateBatch($data, array $conditions)
	{
		!$this->useTransaction ? : $this->db->getTransaction(true)->start();

		try
		{
			$result = $this->db->getWriter()->updateBatch($this->table, $data, $conditions);
		}
		catch (\Exception $e)
		{
			!$this->useTransaction ? : $this->db->getTransaction(true)->rollback();

			throw $e;
		}
		catch (\Throwable $e)
		{
			!$this->useTransaction ? : $this->db->getTransaction(true)->rollback();

			throw $e;
		}

		!$this->useTransaction ? : $this->db->getTransaction(true)->commit();

		return $result;
	}

	/**
	 * Do flush action, this method should be override by sub class.
	 *
	 * @param   mixed $dataset    Data set contain data we want to update.
	 * @param   mixed $conditions Where conditions, you can use array or Compare object.
	 *
	 * @return  mixed
	 * 
	 * @throws \Exception
	 * @throws \Throwable
	 */
	protected function doFlush($dataset, array $conditions)
	{
		!$this->useTransaction ? : $this->db->getTransaction(true)->start();

		try
		{
			if (!$this->delete($conditions))
			{
				throw new \RuntimeException(sprintf('Delete row fail when updating relations table: %s', $this->table));
			}

			if (!$this->create($dataset))
			{
				throw new \RuntimeException(sprintf('Insert row fail when updating relations table: %s', $this->table));
			}
		}
		catch (\Exception $e)
		{
			!$this->useTransaction ? : $this->db->getTransaction(true)->rollback();

			throw $e;
		}
		catch (\Throwable $e)
		{
			!$this->useTransaction ? : $this->db->getTransaction(true)->rollback();

			throw $e;
		}

		!$this->useTransaction ? : $this->db->getTransaction(true)->commit();

		return $dataset;
	}

	/**
	 * Do delete action, this method should be override by sub class.
	 *
	 * @param   mixed $conditions Where conditions, you can use array or Compare object.
	 *
	 * @return  boolean
	 *
	 * @throws \Exception
	 * @throws \Throwable
	 */
	protected function doDelete(array $conditions)
	{
		!$this->useTransaction ? : $this->db->getTransaction(true)->start();

		try
		{
			$result = $this->db->getWriter()->delete($this->table, $conditions);
		}
		catch (\Exception $e)
		{
			!$this->useTransaction ? : $this->db->getTransaction(true)->rollback();

			throw $e;
		}
		catch (\Throwable $e)
		{
			!$this->useTransaction ? : $this->db->getTransaction(true)->rollback();

			throw $e;
		}

		!$this->useTransaction ? : $this->db->getTransaction(true)->commit();

		return $result;
	}

	/**
	 * Get DB adapter.
	 *
	 * @return  AbstractDatabaseDriver Db adapter.
	 */
	public function getDb()
	{
		return $this->db;
	}

	/**
	 * Set db adapter.
	 *
	 * @param   AbstractDatabaseDriver $db Db adapter.
	 *
	 * @return  DataMapper  Return self to support chaining.
	 */
	public function setDb(AbstractDatabaseDriver $db)
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
	public function getFields($table = null)
	{
		if ($this->fields !== null)
		{
			return $this->fields;
		}

		$table = $table ? : $this->table;

		$fields = $this->db->getTable($table)->getColumnDetails();

		foreach ($fields as $field)
		{
			if (strtolower($field->Null) == 'no' && $field->Default === null
				&& $field->Key != 'PRI' && $this->getKeyName() != $field->Field)
			{
				$type = $field->Type;

				list($type,) = explode('(', $type, 2);
				$type = strtolower($type);

				$field->Default = $this->db->getTable($table)->getDataType()->getDefaultValue($type);
			}

			$field = (object) $field;
			$this->fields[$field->Field] = $field;
		}

		return $this->fields;
	}

	/**
	 * If creating item or updating with null values, we must check all NOT NULL
	 * fields are using valid default value instead NULL value. This helps us get rid of
	 * this Mysql warning in STRICT_TRANS_TABLE mode.
	 *
	 * @param Entity $entity
	 *
	 * @return  Entity
	 */
	protected function prepareDefaultValue(Entity $entity)
	{
		foreach ($entity->getFields() as $field => $detail)
		{
			// This field is null and the db column is not nullable, use db default value.
			if ($entity[$field] === null && strtolower($detail->Null) == 'no')
			{
				$entity[$field] = $detail->Default;
			}
		}

		return $entity;
	}
}
