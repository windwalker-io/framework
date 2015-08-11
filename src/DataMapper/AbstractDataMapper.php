<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DataMapper;

use Windwalker\Data\Data;
use Windwalker\Data\DataInterface;
use Windwalker\Data\DataSet;
use Windwalker\Data\DataSetInterface;
use Windwalker\Event\Dispatcher;
use Windwalker\Event\DispatcherInterface;
use Windwalker\Event\Event;
use Windwalker\Event\ListenerMapper;

/**
 * Abstract DataMapper.
 *
 * The class can implement by any database system.
 *
 * @since  2.0
 */
abstract class AbstractDataMapper implements DataMapperInterface
{
	const UPDATE_NULLS = true;

	/**
	 * Table name.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $table = null;

	/**
	 * Primary key.
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $pk = null;

	/**
	 * Table fields.
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $fields = null;

	/**
	 * Property selectFields.
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $selectFields = null;

	/**
	 * Data object class.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $dataClass = 'Windwalker\\Data\\Data';

	/**
	 * Data set object class.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $datasetClass = 'Windwalker\\Data\\DataSet';

	/**
	 * Property useTransaction.
	 *
	 * @var    boolean
	 * @since  2.0
	 */
	protected $useTransaction = true;

	/**
	 * Property dispatcher.
	 *
	 * @var  DispatcherInterface
	 */
	protected $dispatcher;

	/**
	 * Init this class.
	 *
	 * We don't dependency on database in abstract class, that means you can use other data provider.
	 *
	 * @param   string  $table  Table name.
	 * @param   string  $pk     The primary key.
	 *
	 * @throws  \Exception
	 * @since   2.0
	 */
	public function __construct($table = null, $pk = 'id')
	{
		$this->table = $this->table ? : $table;
		$this->pk = $this->pk ? : $pk;

		if (!$this->table)
		{
			throw new \Exception('Hey, please give me a table name~!');
		}

		// Set some custom configuration.
		$this->prepare();

		$this->initialise();
	}

	/**
	 * This method can be override by sub class to prepare come custom setting.
	 *
	 * @return  void
	 * @since   2.0
	 *
	 * @deprecated  Use initialise instead.
	 */
	protected function prepare()
	{
		// Override this method to to something.
	}

	/**
	 * This method can be override by sub class to prepare come custom setting.
	 *
	 * @return  void
	 * @since   2.0
	 */
	protected function initialise()
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
	 * @param   mixed    $conditions Where conditions, you can use array or Compare object.
	 *                               Example:
	 *                               - `array('id' => 5)` => id = 5
	 *                               - `new GteCompare('id', 20)` => 'id >= 20'
	 *                               - `new Compare('id', '%Flower%', 'LIKE')` => 'id LIKE "%Flower%"'
	 * @param   mixed    $order      Order sort, can ba string, array or object.
	 *                               Example:
	 *                               - `id ASC` => ORDER BY id ASC
	 *                               - `array('catid DESC', 'id')` => ORDER BY catid DESC, id
	 * @param   integer  $start      Limit start number.
	 * @param   integer  $limit      Limit rows.
	 *
	 * @return  mixed|DataSet Found rows data set.
	 * @since   2.0
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

		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'conditions' => &$conditions,
			'order'      => &$order,
			'start'      => &$start,
			'limit'      => &$limit
		));

		// Find data
		$result = $this->doFind($conditions, $order, $start, $limit) ? : array();

		foreach ($result as $key => $data)
		{
			if (!($data instanceof $this->dataClass))
			{
				$result[$key] = $this->bindData($data);
			}
		}

		$result = $this->bindDataset($result);

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__), array(
			'result' => &$result,
		));

		return $result;
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
	 * @return mixed|DataSet Found rows data set.
	 */
	public function findAll($order = null, $start = null, $limit = null)
	{
		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'order'      => &$order,
			'start'      => &$start,
			'limit'      => &$limit
		));

		$result = $this->find(array(), $order, $start, $limit);

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__), array(
			'result' => &$result,
		));

		return $result;
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
	 * @return mixed|Data Found row data.
	 */
	public function findOne($conditions = array(), $order = null)
	{
		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'conditions' => &$conditions,
			'order'      => &$order
		));

		$dataset = $this->find($conditions, $order, 0, 1);

		$result = $dataset[0];

		if (!$result)
		{
			$result = new $this->dataClass;
		}

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__), array(
			'result' => &$result,
		));

		return $result;
	}

	/**
	 * Find column as an array.
	 *
	 * @param string  $column     The column we want to select.
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
	 * @return  mixed
	 *
	 * @throws \InvalidArgumentException
	 */
	public function findColumn($column, $conditions = array(), $order = null, $start = null, $limit = null)
	{
		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'column'     => &$column,
			'conditions' => &$conditions,
			'order'      => &$order,
			'start'      => &$start,
			'limit'      => &$limit
		));

		if (!is_string($column))
		{
			throw new \InvalidArgumentException('Column name should be string.');
		}

		$bakSelect = $this->selectFields;

		$this->setSelectFields($column);

		$dataset = $this->find($conditions, $order, $start, $limit);

		$this->setSelectFields($bakSelect);

		$result = array();

		foreach ($dataset as $data)
		{
			$result[] = $data->$column;
		}

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__), array(
			'result' => &$result,
		));

		return $result;
	}

	/**
	 * Create records by data set.
	 *
	 * @param mixed $dataset The data set contains data we want to store.
	 *
	 * @throws \UnexpectedValueException
	 * @throws \InvalidArgumentException
	 * @return  mixed|DataSet  Data set data with inserted id.
	 */
	public function create($dataset)
	{
		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'dataset' => &$dataset
		));

		if (!($dataset instanceof \Traversable) && !is_array($dataset))
		{
			throw new \InvalidArgumentException('DataSet object should be instance of a Traversable');
		}

		$result = $this->doCreate($dataset);

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__), array(
			'result' => &$result,
		));

		return $result;
	}

	/**
	 * Create one record by data object.
	 *
	 * @param mixed $data Send a data in and store.
	 *
	 * @throws \InvalidArgumentException
	 * @return  mixed|Data Data with inserted id.
	 */
	public function createOne($data)
	{
		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'data' => &$data
		));

		$dataset = $this->create($this->bindDataset(array($data)));

		$result = $dataset[0];

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__), array(
			'result' => &$result,
		));

		return $result;
	}

	/**
	 * Update records by data set. Every data depend on this table's primary key to update itself.
	 *
	 * @param mixed $dataset      Data set contain data we want to update.
	 * @param array $condFields   The where condition tell us record exists or not, if not set,
	 *                            will use primary key instead.
	 * @param bool  $updateNulls  Update empty fields or not.
	 *
	 * @return mixed|DataSet
	 */
	public function update($dataset, $condFields = null, $updateNulls = false)
	{
		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'dataset'     => &$dataset,
			'condFields'  => &$condFields,
			'updateNulls' => &$updateNulls
		));

		if (!($dataset instanceof \Traversable) && !is_array($dataset))
		{
			throw new \InvalidArgumentException('DataSet object should be instance of a Traversable');
		}

		// Handling conditions
		$condFields = $condFields ? : $this->getPrimaryKey();

		$result = $this->doUpdate($dataset, (array) $condFields, $updateNulls);

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__), array(
			'result' => &$result,
		));

		return $result;
	}

	/**
	 * Same as update(), just update one row.
	 *
	 * @param mixed $data         The data we want to update.
	 * @param array $condFields   The where condition tell us record exists or not, if not set,
	 *                            will use primary key instead.
	 * @param bool  $updateNulls  Update empty fields or not.
	 *
	 * @return mixed|Data
	 */
	public function updateOne($data, $condFields = null, $updateNulls = false)
	{
		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'data'        => &$data,
			'condFields'  => &$condFields,
			'updateNulls' => &$updateNulls
		));

		$dataset = $this->update($this->bindDataset(array($data)), $condFields, $updateNulls);

		$result = $dataset[0];

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__), array(
			'result' => &$result,
		));

		return $result;
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
	 * @return  boolean
	 */
	public function updateAll($data, $conditions = array())
	{
		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'data'        => &$data,
			'conditions'  => &$conditions
		));

		$result = $this->doUpdateAll($data, $conditions);

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__), array(
			'result' => &$result,
		));

		return $result;
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
	 * @return  mixed|DataSet Updated data set.
	 */
	public function flush($dataset, $conditions = array())
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

		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'dataset'     => &$dataset,
			'conditions'  => &$conditions
		));

		$result = $this->doFlush($dataset, (array) $conditions);

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__), array(
			'result' => &$result,
		));

		return $result;
	}

	/**
	 * Save will auto detect is conditions matched in data or not.
	 * If matched, using update, otherwise we will create it as new record.
	 *
	 * @param mixed $dataset      The data set contains data we want to save.
	 * @param array $condFields   The where condition tell us record exists or not, if not set,
	 *                            will use primary key instead.
	 * @param bool  $updateNulls  Update empty fields or not.
	 *
	 * @return  mixed|DataSet Saved data set.
	 */
	public function save($dataset, $condFields = null, $updateNulls = false)
	{
		// Handling conditions
		$condFields = $condFields ? : $this->getPrimaryKey();

		$condFields = (array) $condFields;

		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'dataset'     => &$dataset,
			'condFields'  => &$condFields,
			'updateNulls' => &$updateNulls
		));

		$createDataset = new $this->datasetClass;
		$updateDataset = new $this->datasetClass;

		foreach ($dataset as $k => $data)
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

			$dataset[$k] = $data;
		}

		$this->create($createDataset);

		$this->update($updateDataset, $condFields, $updateNulls);

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__), array(
			'result' => &$dataset,
		));

		return $dataset;
	}

	/**
	 * Save only one row.
	 *
	 * @param mixed $data         The data we want to save.
	 * @param array $condFields   The where condition tell us record exists or not, if not set,
	 *                            will use primary key instead.
	 * @param bool  $updateNulls  Update empty fields or not.
	 *
	 * @return  mixed|Data Saved data.
	 */
	public function saveOne($data, $condFields = null, $updateNulls = false)
	{
		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'data'        => &$data,
			'condFields'  => &$condFields,
			'updateNulls' => &$updateNulls
		));

		$dataset = $this->save($this->bindDataset(array($data)), $condFields, $updateNulls);

		$result = $dataset[0];

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__), array(
			'result' => &$result,
		));

		return $result;
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

		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'conditions'  => &$conditions
		));

		$result = $this->doDelete($conditions);

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__), array(
			'result' => &$result,
		));

		return $result;
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
	 * @param mixed $dataset      Data set contain data we want to update.
	 * @param array $condFields   The where condition tell us record exists or not, if not set,
	 *                            will use primary key instead.
	 * @param bool  $updateNulls  Update empty fields or not.
	 *
	 * @return  mixed Updated data set.
	 */
	abstract protected function doUpdate($dataset, array $condFields, $updateNulls = false);

	/**
	 * Do updateAll action, this method should be override by sub class.
	 *
	 * @param mixed $data       The data we want to update to every rows.
	 * @param mixed $conditions Where conditions, you can use array or Compare object.
	 *
	 * @return  boolean
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

		if ($object instanceof DataSetInterface)
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

	/**
	 * To use transaction or not.
	 *
	 * @param boolean $yn Yes or no, keep default that we get this value.
	 *
	 * @return  boolean
	 */
	public function useTransaction($yn = null)
	{
		if ($yn !== null)
		{
			$this->useTransaction = (boolean) $yn;
		}

		return $this->useTransaction;
	}

	/**
	 * Method to get property SelectFields
	 *
	 * @return  array
	 */
	public function getSelectFields()
	{
		return $this->selectFields;
	}

	/**
	 * Method to set property selectFields
	 *
	 * @param   array $selectFields
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setSelectFields($selectFields)
	{
		$this->selectFields = (array) $selectFields;

		return $this;
	}

	/**
	 * triggerEvent
	 *
	 * @param   string|Event  $event
	 * @param   array         $args
	 *
	 * @return  Event
	 */
	public function triggerEvent($event, $args = array())
	{
		$dispatcher = $this->getDispatcher();

		if (!$dispatcher instanceof DispatcherInterface)
		{
			return null;
		}

		$args['mapper'] = $this;

		$event = $this->dispatcher->triggerEvent($event, $args);

		$innerListener = array($this, $event->getName());

		if (!$event->isStopped() && is_callable($innerListener))
		{
			call_user_func($innerListener, $event);
		}

		return $event;
	}

	/**
	 * Method to get property Dispatcher
	 *
	 * @return  DispatcherInterface
	 */
	public function getDispatcher()
	{
		if (!$this->dispatcher && class_exists('Windwalker\Event\Dispatcher'))
		{
			$this->dispatcher = new Dispatcher;

			if (is_subclass_of($this, 'Windwalker\Evebt\DispatcherAwareInterface'))
			{
				ListenerMapper::add($this);
			}
		}

		return $this->dispatcher;
	}

	/**
	 * Method to set property dispatcher
	 *
	 * @param   DispatcherInterface $dispatcher
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDispatcher(DispatcherInterface $dispatcher)
	{
		$this->dispatcher = $dispatcher;

		return $this;
	}
}
