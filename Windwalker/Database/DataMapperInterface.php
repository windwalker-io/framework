<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\DataMapper;

/**
 * DataMapper Interface
 */
interface DataMapperInterface
{
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
	public function find($conditions = array(), $order = null, $start = null, $limit = null);

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
	public function findAll($order = null, $start = null, $limit = null);

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
	public function findOne($conditions = array(), $order = null);

	/**
	 * Create records by data set.
	 *
	 * @param mixed $dataset The data set contains data we want to store.
	 *
	 * @return  mixed  Data set data with inserted id.
	 */
	public function create($dataset);

	/**
	 * Create one record by data object.
	 *
	 * @param mixed $data Send a data in and store.
	 *
	 * @return  mixed Data with inserted id.
	 */
	public function createOne($data);

	/**
	 * Update records by data set. Every data depend on this table's primary key to update itself.
	 *
	 * @param mixed $dataset    Data set contain data we want to update.
	 * @param array $condFields The where condition tell us record exists or not, if not set,
	 *                          will use primary key instead.
	 *
	 * @return  mixed Updated data set.
	 */
	public function update($dataset, $condFields = null);

	/**
	 * Using one data to update multiple rows, filter by where conditions.
	 *
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
	 * @return  mixed Updated data set.
	 */
	public function UpdateAll($data, $conditions = array());

	/**
	 * Same as update(), just update one row.
	 *
	 * @param mixed $data       The data we want to update.
	 * @param array $condFields The where condition tell us record exists or not, if not set,
	 *                          will use primary key instead.
	 *
	 * @return  mixed Updated data.
	 */
	public function updateOne($data, $condFields = null);

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
	public function flush($dataset, $conditions = array());

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
	public function save($dataset, $condFields = null);

	/**
	 * Save only one row.
	 *
	 * @param mixed $data       The data we want to save.
	 * @param array $condFields The where condition tell us record exists or not, if not set,
	 *                          will use primary key instead.
	 *
	 * @return  mixed Saved data.
	 */
	public function saveOne($data, $condFields = null);

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
	public function delete($conditions);
}
