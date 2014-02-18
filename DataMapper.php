<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\DataMapper;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\Query\QueryElement;
use Windwalker\DataMapper\Compare\StringCompare;
use Windwalker\DataMapper\Database\DatabaseFactory;

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
	 * Constructor
	 *
	 * @param null           $table
	 * @param string         $pk
	 * @param DatabaseDriver $db
	 */
	public function __construct($table = null, $pk = 'id', DatabaseDriver $db = null)
	{
		$this->db = $db ? : DatabaseFactory::getDbo();

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
		$this->buildConditions($query, $conditions);

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
		$this->db->transactionStart();

		try
		{
			foreach ($dataset as &$data)
			{
				$this->db->insertObject($this->table, $data, $this->getPrimaryKey());
			}
		}
		catch (\Exception $e)
		{
			$this->db->transactionRollback();

			throw $e;
		}

		$this->db->transactionCommit();

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
		$this->db->transactionStart();

		try
		{
			foreach ($dataset as &$data)
			{
				$this->db->updateObject($this->table, $data, $this->getPrimaryKey());
			}
		}
		catch (\Exception $e)
		{
			$this->db->transactionRollback();

			throw $e;
		}

		$this->db->transactionCommit();

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
		$this->db->transactionStart();

		$query = $this->db->getQuery(true);

		// Conditions.
		$this->buildConditions($query, $conditions);

		// Build update values.
		foreach ((array) $data as $field => $value)
		{
			$query->set($query->format('%n = %q', $field, $value));
		}

		$query->update($this->table);

		try
		{
			$result = (boolean) $this->db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			$this->db->transactionRollback();

			throw $e;
		}

		$this->db->transactionCommit();

		return $result;
	}

	/**
	 * doDelete
	 *
	 * @param array $conditions
	 *
	 * @return  mixed
	 */
	protected function doDelete($conditions)
	{
		$query = $this->db->getQuery(true);

		// Conditions.
		$this->buildConditions($query, $conditions);

		$query->delete($this->table);

		return (boolean) $this->db->setQuery($query)->execute();
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
	 * buildConditions
	 *
	 * @param DatabaseQuery &$query
	 * @param array         $conditions
	 *
	 * @return  DatabaseQuery
	 */
	public function buildConditions(DatabaseQuery &$query, array $conditions)
	{
		foreach ($conditions as $key => $value)
		{
			if (empty($value))
			{
				continue;
			}

			// If using Compare class, we convert it to string
			if ($value instanceof StringCompare)
			{
				$query->where((string) $this->buildCompare($key, $value));
			}
			// If is array or object, we use "IN" condition.
			elseif (is_array($value) || is_object($value))
			{
				$value = array_map(array($query, 'quote'), (array) $value);

				$query->where($query->quoteName($key) . new QueryElement('IN ()', $value, ','));
			}
			// Otherwise, we use equal condition.
			else
			{
				$query->where($query->format('%n = %q', $key, $value));
			}
		}

		return $query;
	}

	/**
	 * buildCompare
	 *
	 * @param string|int    $key
	 * @param StringCompare $value
	 *
	 * @return  string
	 */
	public function buildCompare($key, StringCompare $value)
	{
		$query = $this->db->getQuery(true);

		if (!is_numeric($key))
		{
			$value->setCompare1($key);
		}

		$value->setHandler(
			function($compare1, $compare2, $operator) use ($query)
			{
				return $query->format('%n ' . $operator . ' %q', $compare1, $compare2);
			}
		);

		return (string) $value;
	}
}
