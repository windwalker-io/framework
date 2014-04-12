<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\DataMapper;

use Windwalker\Data\DataInterface;
use Windwalker\Data\DatasetInterface;

/**
 * Abstract DataMapper.
 *
 * The class can implement by any database system.
 */
abstract class AbstractDataMapper implements DataMapperInterface
{
	/**
	 * Table name.
	 *
	 * @var  string
	 */
	protected $table = null;

	/**
	 * Primary key.
	 *
	 * @var  array
	 */
	protected $pk = null;

	/**
	 * Table fields.
	 *
	 * @var  array
	 */
	protected $fields = null;

	/**
	 * Data object class.
	 *
	 * @var  string
	 */
	protected $dataClass = 'Windwalker\\Data\\Data';

	/**
	 * Data set object class.
	 *
	 * @var  string
	 */
	protected $datasetClass = 'Windwalker\\Data\\DataSet';

	/**
	 * Init this class.
	 *
	 * We don't dependency on database in abstract class, that means you can use other data provider.
	 *
	 * @param string $table Table name.
	 * @param string $pk    The primary key.
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

		// Set some custom configuration.
		$this->configure();
	}

	/**
	 * This method can be override by sub class to prepare come custom setting.
	 *
	 * @return  void
	 */
	protected function configure()
	{
		// Override this method to to something.
	}

	/**
	 * Find records and return data set.
	 *
	 * Example:
	 * - `$mapper->find(array('id' => 5), 'date', 20, 10);`
	 * - `$mapper->find(null, 'id', 0, 1);`
	 *
	 * @param mixed   $conditions Where conditions, you can use array or Compare object.
	 *                            Example:
	 *                            - `array('id' => 5)` => id = 5
	 *                            - `new GteCompare('id', 20)` => 'id >= 20'
	 *                            - `new Compare('id', '%Flower%', 'LIKE')` => 'id LIKE "%Flower%"'
	 * @param mixed   $order      Order sort, can ba string, array or object.
	 *                            Example:
	 *                            - `id ASC` => ORDER BY id ASC
	 *                            - `array('catid DESC', 'id')` => ORDER BY catid DESC, id
	 * @param integer $start      Limit start number.
	 * @param integer $limit      Limit rows.
	 *
	 * @return mixed Found rows data set.
	 */
	public function find($conditions = array(), $order = null, $start = null, $limit = null)
	{
		// Handling conditions
		if (!is_array($conditions) && !is_object($conditions))
		{
			$cond = array();

			foreach ((array) $this->getPrimaryKey() as $field)
			{
				$cond[$field] = $conditions;
			}

			$conditions = $cond;
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
	 * Find records without where conditions and return data set.
	 *
	 * Same as `$mapper->find(null, 'id', $start, $limit);`
	 *
	 * @param mixed   $order Order sort, can ba string, array or object.
	 *                       Example:
	 *                       - 'id ASC' => ORDER BY id ASC
	 *                       - array('catid DESC', 'id') => ORDER BY catid DESC, id
	 * @param integer $start Limit start number.
	 * @param integer $limit Limit rows.
	 *
	 * @return mixed Found rows data set.
	 */
	public function findAll($order = null, $start = null, $limit = null)
	{
		return $this->find(array(), $order, $start, $limit);
	}

	/**
	 * Find one record and return a data.
	 *
	 * Same as `$mapper->find($conditions, 'id', 0, 1);`
	 *
	 * @param mixed $conditions Where conditions, you can use array or Compare object.
	 *                          Example:
	 *                          - `array('id' => 5)` => id = 5
	 *                          - `new GteCompare('id', 20)` => 'id >= 20'
	 *                          - `new Compare('id', '%Flower%', 'LIKE')` => 'id LIKE "%Flower%"'
	 * @param mixed $order      Order sort, can ba string, array or object.
	 *                          Example:
	 *                          - `id ASC` => ORDER BY id ASC
	 *                          - `array('catid DESC', 'id')` => ORDER BY catid DESC, id
	 *
	 * @return mixed Found row data.
	 */
	public function findOne($conditions = array(), $order = null)
	{
		$dataset = $this->find($conditions, $order, 0, 1);

		if (count($dataset))
		{
			return $dataset[0];
		}

		return new $this->dataClass;
	}

	/**
	 * Create records by data set.
	 *
	 * @param mixed $dataset The data set contains data we want to store.
	 *
	 * @throws \UnexpectedValueException
	 * @throws \InvalidArgumentException
	 * @return  mixed  Data set data with inserted id.
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
	 * Create one record by data object.
	 *
	 * @param mixed $data Send a data in and store.
	 *
	 * @throws \InvalidArgumentException
	 * @return  mixed Data with inserted id.
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
	 * Update records by data set. Every data depend on this table's primary key to update itself.
	 *
	 * @param mixed $dataset    Data set contain data we want to update.
	 * @param array $condFields The where condition tell us record exists or not, if not set,
	 *                          will use primary key instead.
	 *
	 * @throws \UnexpectedValueException
	 * @throws \InvalidArgumentException
	 * @return  mixed Updated data set.
	 */
	public function update($dataset, $condFields = null)
	{
		if (!($dataset instanceof $this->datasetClass))
		{
			throw new \InvalidArgumentException('DataSet object should be: ' . $this->datasetClass);
		}

		// Handling conditions
		$condFields = $condFields ? : $this->getPrimaryKey();

		$dataset = $this->doUpdate($dataset, (array) $condFields);

		if (!($dataset instanceof $this->datasetClass))
		{
			throw new \UnexpectedValueException('Return value should be: ' . $this->datasetClass);
		}

		return $dataset;
	}

	/**
	 * Same as update(), just update one row.
	 *
	 * @param mixed $data       The data we want to update.
	 * @param array $condFields The where condition tell us record exists or not, if not set,
	 *                          will use primary key instead.
	 *
	 * @throws \InvalidArgumentException
	 * @return  mixed Updated data.
	 */
	public function updateOne($data, $condFields = null)
	{
		if (!($data instanceof $this->dataClass))
		{
			throw new \InvalidArgumentException('Data object should be: ' . $this->dataClass);
		}

		$dataset = $this->update($this->bindDataset(array($data)), $condFields);

		return $dataset[0];
	}

	/**
	 * Using one data to update multiple rows, filter by where conditions.
	 * Example:
	 * `$mapper->updateAll(new Data(array('published' => 0)), array('date' => '2014-03-02'))`
	 * Means we make every records which date is 2014-03-02 unpublished.
	 *
	 * @param mixed $data       The data we want to update to every rows.
	 * @param mixed $conditions Where conditions, you can use array or Compare object.
	 *                          Example:
	 *                          - `array('id' => 5)` => id = 5
	 *                          - `new GteCompare('id', 20)` => 'id >= 20'
	 *                          - `new Compare('id', '%Flower%', 'LIKE')` => 'id LIKE "%Flower%"'
	 *
	 * @throws \InvalidArgumentException
	 * @return  mixed Updated data set.
	 */
	public function updateAll($data, $conditions = array())
	{
		if (!($data instanceof $this->dataClass))
		{
			throw new \InvalidArgumentException('Data object should be: ' . $this->dataClass);
		}

		return $this->doUpdateAll($data, $conditions);
	}

	/**
	 * Flush records, will delete all by conditions then recreate new.
	 *
	 * @param mixed $dataset    Data set contain data we want to update.
	 * @param mixed $conditions Where conditions, you can use array or Compare object.
	 *                          Example:
	 *                          - `array('id' => 5)` => id = 5
	 *                          - `new GteCompare('id', 20)` => 'id >= 20'
	 *                          - `new Compare('id', '%Flower%', 'LIKE')` => 'id LIKE "%Flower%"'
	 *
	 * @return  mixed Updated data set.
	 */
	public function flush($dataset, $conditions = array())
	{
		if (!($dataset instanceof $this->datasetClass))
		{
			$dataset = $this->bindDataset($dataset);
		}

		// Handling conditions
		if (!is_array($conditions) && !is_object($conditions))
		{
			$cond = array();

			foreach ((array) $this->getPrimaryKey() as $field)
			{
				$cond[$field] = $conditions;
			}

			$conditions = $cond;
		}

		return $this->doFlush($dataset, (array) $conditions);
	}

	/**
	 * Save will auto detect is conditions matched in data or not.
	 * If matched, using update, otherwise we will create it as new record.
	 *
	 * @param mixed $dataset    The data set contains data we want to save.
	 * @param array $condFields The where condition tell us record exists or not, if not set,
	 *                          will use primary key instead.
	 *
	 * @return  mixed Saved data set.
	 */
	public function save($dataset, $condFields = null)
	{
		// Handling conditions
		$condFields = $condFields ? : $this->getPrimaryKey();

		$condFields = (array) $condFields;

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
			foreach ($condFields as $field)
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

		$this->update($updateDataset, $condFields);

		return $dataset;
	}

	/**
	 * Save only one row.
	 *
	 * @param mixed $data       The data we want to save.
	 * @param array $condFields The where condition tell us record exists or not, if not set,
	 *                          will use primary key instead.
	 *
	 * @return  mixed Saved data.
	 */
	public function saveOne($data, $condFields = null)
	{
		$dataset = $this->save($this->bindDataset(array($data)), $condFields);

		return $dataset[0];
	}

	/**
	 * Delete records by where conditions.
	 *
	 * @param mixed   $conditions Where conditions, you can use array or Compare object.
	 *                            Example:
	 *                            - `array('id' => 5)` => id = 5
	 *                            - `new GteCompare('id', 20)` => 'id >= 20'
	 *                            - `new Compare('id', '%Flower%', 'LIKE')` => 'id LIKE "%Flower%"'
	 *
	 * @return  boolean Will be always true.
	 */
	public function delete($conditions)
	{
		// Handling conditions
		if (!is_array($conditions) && !is_object($conditions))
		{
			$cond = array();

			foreach ((array) $this->getPrimaryKey() as $field)
			{
				$cond[$field] = $conditions;
			}

			$conditions = $cond;
		}

		$conditions = (array) $conditions;

		return $this->doDelete($conditions);
	}

	/**
	 * Do find action, this method should be override by sub class.
	 *
	 * @param array   $conditions Where conditions, you can use array or Compare object.
	 * @param array   $orders     Order sort, can ba string, array or object.
	 * @param integer $start      Limit start number.
	 * @param integer $limit      Limit rows.
	 *
	 * @return  mixed Found rows data set.
	 */
	abstract protected function doFind(array $conditions, array $orders, $start, $limit);

	/**
	 * Do create action, this method should be override by sub class.
	 *
	 * @param mixed $dataset The data set contains data we want to store.
	 *
	 * @return  mixed  Data set data with inserted id.
	 */
	abstract protected function doCreate($dataset);

	/**
	 * Do update action, this method should be override by sub class.
	 *
	 * @param mixed $dataset    Data set contain data we want to update.
	 * @param array $condFields The where condition tell us record exists or not, if not set,
	 *                          will use primary key instead.
	 *
	 * @return  mixed Updated data set.
	 */
	abstract protected function doUpdate($dataset, array $condFields);

	/**
	 * Do updateAll action, this method should be override by sub class.
	 *
	 * @param mixed $data       The data we want to update to every rows.
	 * @param mixed $conditions Where conditions, you can use array or Compare object.
	 *
	 * @return  mixed Updated data set.
	 */
	abstract protected function doUpdateAll($data, array $conditions);

	/**
	 * Do flush action, this method should be override by sub class.
	 *
	 * @param mixed $dataset    Data set contain data we want to update.
	 * @param mixed $conditions Where conditions, you can use array or Compare object.
	 *
	 * @return  mixed Updated data set.
	 */
	abstract protected function doFlush($dataset, array $conditions);

	/**
	 * Do delete action, this method should be override by sub class.
	 *
	 * @param mixed   $conditions Where conditions, you can use array or Compare object.
	 *
	 * @return  boolean Will be always true.
	 */
	abstract protected function doDelete(array $conditions);

	/**
	 * Get primary key.
	 *
	 * @return  array|string Primary key.
	 */
	public function getPrimaryKey()
	{
		return $this->pk ? : 'id';
	}

	/**
	 * Get table name.
	 *
	 * @return  string Table name.
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Set table name.
	 *
	 * @param   string $table Table name.
	 *
	 * @return  AbstractDataMapper  Return self to support chaining.
	 */
	public function setTable($table)
	{
		$this->table = $table;

		return $this;
	}

	/**
	 * Bind a record into data.
	 *
	 * @param mixed $data The data we want to bind.
	 *
	 * @return  object
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
	 * Bind records into data set.
	 *
	 * @param mixed $dataset Data set we want to bind.
	 *
	 * @return  object Data set object.
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
	 * Get data class.
	 *
	 * @return  string Dat class.
	 */
	public function getDataClass()
	{
		return $this->dataClass;
	}

	/**
	 * Set data class.
	 *
	 * @param   string $dataClass Data class.
	 *
	 * @return  AbstractDataMapper  Return self to support chaining.
	 */
	public function setDataClass($dataClass)
	{
		$this->dataClass = $dataClass;

		return $this;
	}

	/**
	 * Get data set class.
	 *
	 * @return  string Data set class.
	 */
	public function getDatasetClass()
	{
		return $this->datasetClass;
	}

	/**
	 * Set Data set class.
	 *
	 * @param   string $datasetClass Dat set class.
	 *
	 * @return  AbstractDataMapper  Return self to support chaining.
	 */
	public function setDatasetClass($datasetClass)
	{
		$this->datasetClass = $datasetClass;

		return $this;
	}
}
