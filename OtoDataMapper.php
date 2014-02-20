<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\DataMapper;

use Windwalker\DataMapper\Database\QueryHelper;

/**
 * Class OtoDataMapper
 *
 * @since 1.0
 */
class OtoDataMapper extends DataMapper
{
	/**
	 * Property relations.
	 *
	 * @var  array
	 */
	protected $relations = array();

	/**
	 * addRelation
	 *
	 * @param string $field
	 * @param mixed  $table
	 * @param mixed  $relations
	 *
	 * @throws \InvalidArgumentException
	 * @return  OtoDataMapper
	 */
	public function addRelation($field, $table, $relations)
	{
		if (is_string($table))
		{
			$table = new DataMapper($table, null, $this->db);
		}
		elseif (!($table instanceof DataMapperInterface))
		{
			throw new \InvalidArgumentException('Argument 2, table should be string or DataMapper object');
		}

		$this->relations[$field] = array(
			'field'     => $field,
			'table'     => $table,
			'relations' => $relations
		);

		return $this;
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
		// Do find first
		$dataset = parent::doFind($conditions, $orders, $start, $limit);

		// Loop the relation mapper.
		foreach ($this->relations as $field => $relation)
		{
			// Loop each data.
			foreach ($dataset as &$data)
			{
				// Prepare sub conditions
				$conditions = array();

				// Find relation data to this field.
				foreach ($relation['relations'] as $left => $right)
				{
					$conditions[$right] = $data->$left;
				}

				$data->$field = $relation['table']->findOne($conditions);
			}
		}

		return $dataset;
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
		$this->db->transactionStart(true);

		try
		{
			// Do create first
			$dataset = parent::doCreate($dataset);

			// Loop the relation mapper.
			foreach ($this->relations as $field => $relation)
			{
				// Loop each data.
				foreach ($dataset as &$data)
				{
					// If relation field exists, push the foreign key and save.
					if ($data->$field)
					{
						if (!is_array($data->$field) && !is_object($data->$field))
						{
							throw new \InvalidArgumentException(sprintf('Saving relations %s::$%s need array or object.', get_class($data), $field));
						}

						foreach ($relation['relations'] as $left => $right)
						{
							$data->$field->$right = $data->$left;
						}

						$data->$field = $relation['table']->saveOne($this->bindData($data->$field));
					}
				}
			}
		}
		catch (\Exception $e)
		{
			$this->db->transactionRollback(true);

			throw $e;
		}

		$this->db->transactionCommit(true);

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
		$this->db->transactionStart(true);

		try
		{
			// Do create first
			$dataset = parent::doUpdate($dataset);

			// Loop the relation mapper.
			foreach ($this->relations as $field => $relation)
			{
				// Loop each data.
				foreach ($dataset as &$data)
				{
					// If relation field exists, push the foreign key and save.
					if ($data->$field)
					{
						if (!is_array($data->$field) && !is_object($data->$field))
						{
							throw new \InvalidArgumentException(sprintf('Saving relations %s::$%s need array or object.', get_class($data), $field));
						}

						foreach ($relation['relations'] as $left => $right)
						{
							$data->$field->$right = $data->$left;
						}

						$data->$field = $relation['table']->saveOne($this->bindData($data->$field));
					}
				}
			}
		}
		catch (\Exception $e)
		{
			$this->db->transactionRollback(true);

			throw $e;
		}

		$this->db->transactionCommit(true);

		return $dataset;
	}

	/**
	 * doUpdateAll
	 *
	 * @param $data
	 * @param $conditions
	 *
	 * @throws \LogicException
	 * @return  mixed
	 */
	protected function doUpdateAll($data, $conditions)
	{
		throw new \LogicException(sprintf('%s do not support %s().', __CLASS__, __METHOD__));
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
}
