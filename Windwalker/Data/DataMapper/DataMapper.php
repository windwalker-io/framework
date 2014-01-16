<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Data\DataMapper;

use JArrayHelper;
use JDatabaseDriver;
use JFactory;
use Windwalker\Data\Data;
use Windwalker\Data\DataSet;
use Windwalker\Data\NullData;

/**
 * Class DataMapper
 *
 * @since 1.0
 */
class DataMapper
{
	/**
	 * Property db.
	 *
	 * @var  JDatabaseDriver
	 */
	protected $db = null;

	/**
	 * Property table.
	 *
	 * @var  string
	 */
	protected $table = null;

	/**
	 * Property primaryKey.
	 *
	 * @var  string
	 */
	protected $primaryKey = null;

	/**
	 * Property fields.
	 *
	 * @var  array
	 */
	protected $fields = null;

	/**
	 * Constructor
	 *
	 * @param string          $table
	 * @param JDatabaseDriver $db
	 */
	public function __construct($table = null, JDatabaseDriver $db = null)
	{
		$this->db    = $db ? : JFactory::getDbo();
		$this->table = $table;
	}

	/**
	 * find
	 *
	 * @param mixed $conditions
	 * @param null  $start
	 * @param null  $limit
	 *
	 * @return  DataSet
	 */
	public function find($conditions, $start = null, $limit = null)
	{
		$query = $this->db->getQuery(true);

		// Guessing primary key
		if (!is_array($conditions) && !is_object($conditions))
		{
			$primaryKey = $this->getPrimaryKey();

			$conditions = array($primaryKey => $conditions);
		}

		$conditions = (array) $conditions;

		// Where conditions
		if (count($conditions))
		{
			$query->where($conditions);
		}

		// Build query
		$query->select($this->table)
			->from($this->table);

		$this->db->setQuery($query, $start, $limit);

		return new DataSet($this->db->loadObjectList(null, '\\Windwalker\\Data\\Data'));
	}

	/**
	 * findOne
	 *
	 * @param mixed $conditions
	 *
	 * @return  NullData|Data
	 */
	public function findOne($conditions)
	{
		$dataset = $this->find($conditions);

		if (count($dataset))
		{
			return $dataset[0];
		}
		else
		{
			return new NullData;
		}
	}

	/**
	 * insert
	 *
	 * @param array|DataSet $dataset
	 *
	 * @throws \Exception
	 * @return  bool|mixed
	 */
	public function insert($dataset)
	{
		$query = $this->db->getQuery(true);

		if (!count($dataset))
		{
			return false;
		}

		// Prepare fields
		$fields = $this->getFields();

		$firstData = ($dataset[0] instanceof Data) ? (array) $dataset[0]->dump() : (array) $dataset[0];

		$fields = !count($fields) ? $fields : array_keys($firstData);

		foreach ($dataset as $data)
		{
			$insertData = array();

			// Filter unnecessary fields.
			foreach ($fields as $field)
			{
				$insertData[$field] = $query->quote($data->$field);
			}

			$query->values(implode(', ', $insertData));
		}

		$query->insert($this->table)
			->columns($fields);

		// Do insert
		try
		{
			$this->db->transactionStart();

			$result = $this->db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			$this->db->transactionRollback();

			throw $e;
		}

		$this->db->transactionCommit();

		// Get insert ids
		$lastId = $this->db->setQuery('SELECT LAST_INSERT_ID();')->loadResult();

		if (!$lastId)
		{
			return $result;
		}

		$primaryKey = $this->getPrimaryKey();

		foreach ($dataset as $data)
		{
			$data->$primaryKey = $lastId;

			$lastId++;
		}

		return $result;
	}

	/**
	 * insertOne
	 *
	 * @param Data|array|object $data
	 *
	 * @return  mixed
	 */
	public function insertOne($data)
	{
		if (!($data instanceof Data))
		{
			$data = new Data($data);
		}

		$dataset = $this->insert(array($data));

		return $dataset[0];
	}

	/**
	 * update
	 *
	 * @param array|DataSet $dataset
	 * @param string[]      $conditions
	 *
	 * @throws \Exception
	 * @return  bool
	 */
	public function update($dataset, $conditions = null)
	{
		$query  = $this->db->getQuery(true);

		if (!count($dataset))
		{
			return false;
		}

		// Handling conditions
		$conditions = $conditions ? : $this->getPrimaryKey();

		$conditions = (array) $conditions;

		// Every update use same where condition or by self.
		$sameCondition = JArrayHelper::isAssociative($conditions);

		// Get condition fields.
		$condFields = $sameCondition ? array_keys($conditions) : $conditions;

		// Get table fields
		$fields = $this->getFields();

		if (!count($fields))
		{
			$firstData = ($dataset[0] instanceof Data) ? (array) $dataset[0]->dump() : (array) $dataset[0];

			$fields = array_keys($firstData);
		}

		// Prepare update query.
		$query->update($this->table);

		try
		{
			$this->db->transactionStart();

			foreach ($dataset as $data)
			{
				// Reset query and re add values.
				$query->clear('where')
					->clear('set');

				// Build update data.
				$updateData = array();

				foreach ($fields as $field)
				{
					if (in_array($field, $condFields))
					{
						continue;
					}

					$updateData[] = $query->quoteName($field) . ' = ' . $query->quote($data->$field);
				}

				$query->set(implode(', ', $updateData));

				// Use same where condition or by self.
				if ($sameCondition)
				{
					foreach ($conditions as $key => $value)
					{
						$query->where($query->quoteName($key) . ' = ' . $query->quote($value));
					}
				}
				else
				{
					foreach ($conditions as $condition)
					{
						$query->where($query->quoteName($condition) . ' = ' . $query->quote($data->$condition));
					}
				}

				if (!$this->db->setQuery($query)->execute())
				{
					throw new \Exception('Update fail');
				}
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
	 * updateOne
	 *
	 * @param Data|array $data
	 * @param array      $conditions
	 *
	 * @return  bool
	 */
	public function updateOne($data, $conditions = null)
	{
		if (!($data instanceof Data))
		{
			$data = new Data($data);
		}

		return $this->update(array($data), $conditions);
	}

	/**
	 * save
	 *
	 * @param DataSet|array $dataset
	 * @param array         $conditions
	 *
	 * @return  DataSet|array
	 */
	public function save($dataset, $conditions = null)
	{
		// Handling conditions
		$conditions = $conditions ? : $this->getPrimaryKey();

		$conditions = (array) $conditions;

		$insertDataset = new DataSet;
		$updateDataset = new DataSet;

		foreach ($dataset as &$data)
		{
			if (!($data instanceof Data))
			{
				$data = new Data($data);
			}

			$update = true;

			// If one field not matched, use insert.
			foreach ($conditions as $field)
			{
				if (!$data->$field)
				{
					$update = false;

					break;
				}
			}

			// Do save
			if ($update)
			{
				$updateDataset[] = $data;
			}
			else
			{
				$insertDataset[] = $data;
			}
		}

		$this->insert($insertDataset);

		$this->update($updateDataset, $conditions);

		return $dataset;
	}

	/**
	 * delete
	 *
	 * @param array  $conditions
	 * @param string $glue
	 *
	 * @return  mixed
	 */
	public function delete($conditions, $glue = 'AND')
	{
		$query  = $this->db->getQuery(true);

		// Handling conditions
		if (!is_array($conditions) && !is_object($conditions))
		{
			$conditions = array($this->getPrimaryKey() => $conditions);
		}

		$conditions = (array) $conditions;

		// Where conditions
		foreach ($conditions as $key => $value)
		{
			if (is_array($value) || is_object($value))
			{
				$value = array_map(array($query, 'quote'), (array) $value);

				$query->where($query->quoteName($key) . new \JDatabaseQueryElement('IN ()', $value, ','), $glue);
			}
			else
			{
				$query->where($query->quoteName($key) . ' = ' . $query->quote($value), $glue);
			}
		}

		$query->delete($this->table);

		return $this->db->setQuery($query)->execute();
	}

	/**
	 * getFields
	 *
	 * @return  array
	 */
	public function getFields()
	{
		if (is_null($this->fields))
		{
			$this->fields = array_keys($this->db->getTableColumns($this->table, false));
		}

		return $this->fields;
	}

	/**
	 * getPrimaryKey
	 *
	 * @return  string
	 */
	public function getPrimaryKey()
	{
		return $this->primaryKey ? : 'id';
	}
}
