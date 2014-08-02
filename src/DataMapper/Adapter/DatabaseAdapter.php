<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\DataMapper\Adapter;

/**
 * Class DatabaseAdapter
 *
 * @since 1.0
 */
abstract class DatabaseAdapter
{
	/**
	 * Property instance.
	 *
	 * @var  DatabaseAdapter
	 */
	protected static $instance = null;

	/**
	 * getInstance
	 *
	 * @throws \UnexpectedValueException
	 * @return  DatabaseAdapter
	 */
	public static function getInstance()
	{
		if (!static::$instance)
		{
			throw new \UnexpectedValueException('DB Adapter Not set.');
		}

		if (is_callable(static::$instance))
		{
			static::$instance = call_user_func(static::$instance);
		}

		if (!(static::$instance instanceof DatabaseAdapter))
		{
			throw new \UnexpectedValueException('DB Adapter instance must be callable or extends DatabaseAdapter.');
		}

		return static::$instance;
	}

	/**
	 * setInstance
	 *
	 * @param object|callable $db
	 *
	 * @return  void
	 */
	public static function setInstance($db)
	{
		static::$instance = $db;
	}

	/**
	 * Get DB adapter.
	 *
	 * @return  DatabaseAdapter Db adapter.
	 */
	public function getDb()
	{
		return $this->db;
	}

	/**
	 * Set db adapter.
	 *
	 * @param   DatabaseAdapter $db Db adapter.
	 *
	 * @return  DatabaseAdapter  Return self to support chaining.
	 */
	public function setDb($db)
	{
		$this->db = $db;

		return $this;
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
	abstract public function find($table, array $conditions = array(), array $orders = array(), $start = 0, $limit = null);

	/**
	 * Do create action.
	 *
	 * @param string  $table The table name.
	 * @param mixed   $data  The data set contains data we want to store.
	 * @param string  $pk    The primary key column name.
	 *
	 * @return  mixed  Data set data with inserted id.
	 */
	abstract public function create($table, $data, $pk = null);

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
	abstract public function updateOne($table, $data, array $condFields = array());

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
	abstract public function updateAll($table, $data, array $conditions = array());

	/**
	 * Do delete action, this method should be override by sub class.
	 *
	 * @param string  $table      The table name.
	 * @param mixed   $conditions Where conditions, you can use array or Compare object.
	 *
	 * @throws \Exception
	 * @return  boolean Will be always true.
	 */
	abstract public function delete($table, array $conditions = array());

	/**
	 * Get table fields.
	 *
	 * @param string $table Table name.
	 *
	 * @return  array
	 */
	abstract public function getFields($table);

	/**
	 * transactionStart
	 *
	 * @param bool $asSavePoint
	 *
	 * @return  $this
	 */
	abstract public function transactionStart($asSavePoint = false);

	/**
	 * transactionCommit
	 *
	 * @param bool $asSavePoint
	 *
	 * @return  $this
	 */
	abstract public function transactionCommit($asSavePoint = false);

	/**
	 * transactionRollback
	 *
	 * @param bool $asSavePoint
	 *
	 * @return  $this
	 */
	abstract public function transactionRollback($asSavePoint = false);
}

