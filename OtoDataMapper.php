<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\DataMapper;

/**
 * Class OtoDataMapper
 *
 * @since 1.0
 */
class OtoDataMapper extends RelationDataMapper
{
	/**
	 * doCreate
	 *
	 * @param $dataset
	 *
	 * @return  mixed
	 */
	protected function doCreate($dataset)
	{
		$dataset = parent::doCreate($dataset);

		foreach ($this->mappers as $alias => $mapper)
		{
			foreach ($dataset as &$data)
			{
				if ($data->$alias)
				{
					$data->$alias = $this->createOne($this->bindData($data->alias));
				}
			}
		}

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
		$dataset = parent::doCreate($dataset);

		foreach ($this->mappers as $alias => $mapper)
		{
			foreach ($dataset as &$data)
			{
				if ($data->$alias)
				{
					$data->$alias = $this->updateOne($this->bindData($data->alias));
				}
			}
		}

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
		throw new \LogicException('RelationDataMapper not support UpdateAll() yet.');
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
