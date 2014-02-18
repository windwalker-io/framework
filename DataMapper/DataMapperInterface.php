<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Data\DataMapper;

use Windwalker\Data\Data;
use Windwalker\Data\DataSet;

/**
 * Interface DataMapperInterface
 */
interface DataMapperInterface
{
	/**
	 * find
	 *
	 * @param mixed $conditions
	 * @param null  $order
	 * @param null  $start
	 * @param null  $limit
	 *
	 * @return  mixed
	 */
	public function find($conditions = array(), $order = null, $start = null, $limit = null);

	/**
	 * findAll
	 *
	 * @param int $start
	 * @param int $limit
	 *
	 * @return  mixed
	 */
	public function findAll($order = null, $start = null, $limit = null);

	/**
	 * findOne
	 *
	 * @param mixed $conditions
	 *
	 * @return  mixed
	 */
	public function findOne($conditions = array(), $order = null);

	/**
	 * insert
	 *
	 * @param array $dataset
	 *
	 * @throws  Exception
	 * @return  bool|mixed
	 */
	public function create($dataset);

	/**
	 * insertOne
	 *
	 * @param Data|array|object $data
	 *
	 * @return  mixed
	 */
	public function insertOne($data);

	/**
	 * update
	 *
	 * @param array|DataSet $dataset
	 * @param string[]      $conditions
	 *
	 * @throws  Exception
	 * @return  bool
	 */
	public function update($dataset, $conditions = null);

	/**
	 * updateOne
	 *
	 * @param Data|array $data
	 * @param array      $conditions
	 *
	 * @return  bool
	 */
	public function updateOne($data, $conditions = null);
	/**
	 * save
	 *
	 * @param DataSet|array $dataset
	 * @param array         $conditions
	 *
	 * @return  DataSet|array
	 */
	public function save($dataset, $conditions = null);

	/**
	 * delete
	 *
	 * @param array  $conditions
	 * @param string $glue
	 *
	 * @return  mixed
	 */
	public function delete($conditions);
}
 