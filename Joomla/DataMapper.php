<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Data\Joomla;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\Query\QueryElement;
use Windwalker\Data\Data;
use Windwalker\Data\DataMapper\AbstractDataMapper;

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
	 * @param null                            $table
	 * @param string                          $pk
	 * @param \Joomla\Database\DatabaseDriver $db
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

		// Loop every conditions.
		foreach ($conditions as $key => $value)
		{
			if (empty($value))
			{
				continue;
			}

			// If is array or object, we use "IN" condition.
			if ((is_array($value) || is_object($value)))
			{
				$value = array_map(array($query, 'quote'), (array) $value);

				$query->where($query->quoteName($key) . new QueryElement('IN ()', $value, ','));
			}
			// Otherwise, we use equal condition.
			else
			{
				$query->where($query->quoteName($key) . ' = ' . $query->quote($value));
			}
		}

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
	 * @return  mixed
	 */
	protected function doCreate($dataset)
	{
		// TODO: Implement doCreate() method.
	}

	/**
	 * doUpdate
	 *
	 * @param $dataset
	 * @param $conditions
	 *
	 * @return  mixed
	 */
	protected function doUpdate($dataset, $conditions)
	{
		// TODO: Implement doUpdate() method.
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
		// TODO: Implement doDelete() method.
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
		// TODO: Implement insertOne() method.
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
}
