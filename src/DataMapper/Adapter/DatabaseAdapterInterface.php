<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DataMapper\Adapter;

/**
 * The DatabaseAdapter Interface
 */
interface DatabaseAdapterInterface
{
	/**
	 * Do find action.
	 *
	 * @param string  $table      The table name.
	 * @param string  $select     The select fields, default is '*'.
	 * @param array   $conditions Where conditions, you can use array or Compare object.
	 * @param array   $orders     Order sort, can ba string, array or object.
	 * @param integer $start      Limit start number.
	 * @param integer $limit      Limit rows.
	 * @param array   $options    Other options.
	 *
	 * @return  mixed Found rows data set.
	 */
	public function find($table, $select = '*', array $conditions = array(), array $orders = array(), $start = 0, $limit = null, $options = array());

	/**
	 * Do create action.
	 *
	 * @param string  $table The table name.
	 * @param mixed   $data  The data set contains data we want to store.
	 * @param string  $pk    The primary key column name.
	 *
	 * @return  mixed  Data set data with inserted id.
	 */
	public function create($table, $data, $pk = null);

	/**
	 * Do update action.
	 *
	 * @param string  $table        The table name.
	 * @param mixed   $data         Data set contain data we want to update.
	 * @param array   $condFields   The where condition tell us record exists or not, if not set,
	 *                              will use primary key instead.
	 * @param bool    $updateNulls  Update empty fields or not.
	 *
	 * @throws \Exception
	 * @return  mixed Updated data set.
	 */
	public function updateOne($table, $data, array $condFields = array(), $updateNulls = false);

	/**
	 * Do updateAll action.
	 *
	 * @param string  $table      The table name.
	 * @param mixed   $data       The data we want to update to every rows.
	 * @param mixed   $conditions Where conditions, you can use array or Compare object.
	 *
	 * @throws \Exception
	 * @return  boolean
	 */
	public function updateAll($table, $data, array $conditions = array());

	/**
	 * Do delete action, this method should be override by sub class.
	 *
	 * @param string  $table      The table name.
	 * @param mixed   $conditions Where conditions, you can use array or Compare object.
	 *
	 * @throws \Exception
	 * @return  boolean Will be always true.
	 */
	public function delete($table, array $conditions = array());

	/**
	 * Get table fields.
	 *
	 * @param string $table Table name.
	 *
	 * @return  array
	 */
	public function getFields($table);

	/**
	 * transactionStart
	 *
	 * @param bool $asSavePoint
	 *
	 * @return  $this
	 */
	public function transactionStart($asSavePoint = false);

	/**
	 * transactionCommit
	 *
	 * @param bool $asSavePoint
	 *
	 * @return  $this
	 */
	public function transactionCommit($asSavePoint = false);

	/**
	 * transactionRollback
	 *
	 * @param bool $asSavePoint
	 *
	 * @return  $this
	 */
	public function transactionRollback($asSavePoint = false);
}
