<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Data\DataMapper;

use Windwalker\Data\Data;
use Windwalker\Data\DataInterface;
use Windwalker\Data\DataSet;
use Windwalker\Data\DatasetInterface;

/**
 * Class AbstractDataMapper
 *
 * @since 1.0
 */
abstract class AbstractDataMapper implements DataMapperInterface
{
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
	protected $pk = null;

	/**
	 * Property fields.
	 *
	 * @var  array
	 */
	protected $fields = null;

	/**
	 * Property dataClass.
	 *
	 * @var  string
	 */
	protected $dataClass = 'Windwalker\\Data\\Data';

	/**
	 * Property datasetClass.
	 *
	 * @var  string
	 */
	protected $datasetClass = 'Windwalker\\Data\\DataSet';

	/**
	 * Constructor
	 *
	 * @param string $table
	 * @param string $pk
	 *
	 * @throws \Exception
	 */
	public function __construct($table = null, $pk = 'id')
	{
		if (!$this->table)
		{
			$this->table = $table;
		}

		if (!$this->table)
		{
			throw new \Exception('Hey, please give me a table name~!');
		}

		$this->pk = $pk;
	}

	/**
	 * find
	 *
	 * @param mixed $conditions
	 * @param null  $order
	 * @param null  $start
	 * @param null  $limit
	 *
	 * @return  DataSet
	 */
	public function find($conditions = array(), $order = null, $start = null, $limit = null)
	{
		// Guessing primary key
		if (!is_array($conditions) && !is_object($conditions))
		{
			$primaryKey = $this->getPrimaryKey();

			$conditions = array($primaryKey => $conditions);
		}

		$conditions = (array) $conditions;

		$order = (array) $order;

		// Find data
		$result = $this->doFind($conditions, $order, $start, $limit) ? : array();

		foreach ($result as $key => $data)
		{
			if (!($data instanceof $this->dataClass))
			{
				$result[$key] = $this->bindData($data);
			}
		}

		return $this->bindDataset($result);
	}

	/**
	 * findAll
	 *
	 * @param null $order
	 * @param int  $start
	 * @param int  $limit
	 *
	 * @return  mixed
	 */
	public function findAll($order = null, $start = null, $limit = null)
	{
		return $this->find(array(), $order, $start, $limit);
	}

	/**
	 * findOne
	 *
	 * @param mixed $conditions
	 * @param       $order
	 *
	 * @return  mixed
	 */
	public function findOne($conditions = array(), $order = null)
	{
		$dataset = $this->find($conditions, $order, 0, 1);

		if (count($dataset))
		{
			return $dataset[0];
		}

		// Return NULL dataset
		return new $this->datasetClass;
	}

	/**
	 * insert
	 *
	 * @param array|DataSet $dataset
	 *
	 * @throws \UnexpectedValueException
	 * @throws \InvalidArgumentException
	 * @return  mixed
	 */
	public function create($dataset)
	{
		if (!($dataset instanceof $this->datasetClass))
		{
			throw new \InvalidArgumentException('DataSet object should be: ' . $this->datasetClass);
		}

		$dataset = $this->doCreate($dataset);

		if (!($dataset instanceof $this->datasetClass))
		{
			throw new \UnexpectedValueException('Return value should be: ' . $this->datasetClass);
		}

		return $dataset;
	}

	/**
	 * insertOne
	 *
	 * @param object $data
	 *
	 * @throws \InvalidArgumentException
	 * @return  mixed
	 */
	public function createOne($data)
	{
		if (!($data instanceof $this->dataClass))
		{
			throw new \InvalidArgumentException('Data object should be: ' . $this->dataClass);
		}

		$dataset = $this->create($this->bindDataset(array($data)));

		return $dataset[0];
	}

	/**
	 * update
	 *
	 * @param array|DataSet $dataset
	 *
	 * @throws \UnexpectedValueException
	 * @throws \InvalidArgumentException
	 * @return  bool
	 */
	public function update($dataset)
	{
		if (!($dataset instanceof $this->datasetClass))
		{
			throw new \InvalidArgumentException('DataSet object should be: ' . $this->datasetClass);
		}

		$dataset = $this->doUpdate($dataset);

		if (!($dataset instanceof $this->datasetClass))
		{
			throw new \UnexpectedValueException('Return value should be: ' . $this->datasetClass);
		}

		return $dataset;
	}

	/**
	 * updateOne
	 *
	 * @param Data|array $data
	 *
	 * @throws \InvalidArgumentException
	 * @return  bool
	 */
	public function updateOne($data)
	{
		if (!($data instanceof $this->dataClass))
		{
			throw new \InvalidArgumentException('Data object should be: ' . $this->dataClass);
		}

		$dataset = $this->update($this->bindDataset(array($data)));

		return $dataset[0];
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

		$createDataset = new $this->datasetClass;
		$updateDataset = new $this->datasetClass;

		foreach ($dataset as &$data)
		{
			if (!($data instanceof $this->dataClass))
			{
				$data = $this->bindData($data);
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
				$createDataset[] = $data;
			}
		}

		$this->create($createDataset);

		$this->update($updateDataset, $conditions);

		return $dataset;
	}

	/**
	 * delete
	 *
	 * @param array  $conditions
	 *
	 * @return  mixed
	 */
	public function delete($conditions)
	{
		// Handling conditions
		if (!is_array($conditions) && !is_object($conditions))
		{
			$conditions = array($this->getPrimaryKey() => $conditions);
		}

		$conditions = (array) $conditions;

		return $this->doDelete($conditions);
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
	abstract protected function doFind(array $conditions, array $order, $start, $limit);

	/**
	 * doCreate
	 *
	 * @param $dataset
	 *
	 * @return  mixed
	 */
	abstract protected function doCreate($dataset);

	/**
	 * doUpdate
	 *
	 * @param $dataset
	 * @param $conditions
	 *
	 * @return  mixed
	 */
	abstract protected function doUpdate($dataset);

	/**
	 * doDelete
	 *
	 * @param $conditions
	 *
	 * @return  mixed
	 */
	abstract protected function doDelete($conditions);

	/**
	 * getPrimaryKey
	 *
	 * @return  string
	 */
	public function getPrimaryKey()
	{
		return $this->pk ? : 'id';
	}

	/**
	 * getTable
	 *
	 * @return  string
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * setTable
	 *
	 * @param   string $table
	 *
	 * @return  AbstractDataMapper  Return self to support chaining.
	 */
	public function setTable($table)
	{
		$this->table = $table;

		return $this;
	}

	/**
	 * bindData
	 *
	 * @param object $data
	 *
	 * @return  mixed
	 *
	 * @throws \UnexpectedValueException
	 */
	protected function bindData($data)
	{
		$object = new $this->dataClass;

		if ($object instanceof DataInterface)
		{
			return $object->bind($data);
		}

		foreach ((array) $data as $field => $value)
		{
			$object->$field = $value;
		}

		return $object;
	}

	/**
	 * bindDataset
	 *
	 * @param array  $dataset
	 *
	 * @return  mixed
	 *
	 * @throws \UnexpectedValueException
	 * @throws \InvalidArgumentException
	 */
	protected function bindDataset($dataset)
	{
		$object = new $this->datasetClass;

		if ($object instanceof DatasetInterface)
		{
			return $object->bind($dataset);
		}

		if ($dataset instanceof \Traversable)
		{
			$dataset = iterator_to_array($dataset);
		}
		elseif (is_object($dataset))
		{
			$dataset = array($dataset);
		}
		elseif (!is_array($dataset))
		{
			throw new \InvalidArgumentException(sprintf('Need an array or object in %s::%s()', __CLASS__, __METHOD__));
		}

		foreach ($dataset as $data)
		{
			$object[] = $data;
		}

		return $object;
	}

	/**
	 * getDataClass
	 *
	 * @return  string
	 */
	public function getDataClass()
	{
		return $this->dataClass;
	}

	/**
	 * setDataClass
	 *
	 * @param   string $dataClass
	 *
	 * @return  AbstractDataMapper  Return self to support chaining.
	 */
	public function setDataClass($dataClass)
	{
		$this->dataClass = $dataClass;

		return $this;
	}

	/**
	 * getDatasetClass
	 *
	 * @return  string
	 */
	public function getDatasetClass()
	{
		return $this->datasetClass;
	}

	/**
	 * setDatasetClass
	 *
	 * @param   string $datasetClass
	 *
	 * @return  AbstractDataMapper  Return self to support chaining.
	 */
	public function setDatasetClass($datasetClass)
	{
		$this->datasetClass = $datasetClass;

		return $this;
	}
}
