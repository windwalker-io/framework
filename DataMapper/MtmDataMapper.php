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
 * A DataMapper to handle many-to-many mapping table.
 *
 * @since 1.0
 */
class MtmDataMapper extends DataMapper
{
	/**
	 * update
	 *
	 * @param array|DataSet $dataset
	 * @param array         $conditions
	 *
	 * @return  bool
	 *
	 * @throws \Exception
	 */
	public function update($dataset, $conditions = null)
	{
		foreach ($dataset as &$data)
		{
			if (!($data instanceof $data))
			{
				$data = new Data($data);
			}

			foreach ($conditions as $field => $condition)
			{
				$data->$field = $condition;
			}
		}

		$this->db->transactionStart();

		try
		{
			if (!$this->delete($conditions))
			{
				throw new \Exception(sprintf('Delete row fail when updating relations table: %s', $this->table));
			}

			if (!$this->insert($dataset))
			{
				throw new \Exception(sprintf('Insert row fail when updating relations table: %s', $this->table));
			}
		}
		catch (\Exception $e)
		{
			$this->db->transactionRollback();

			throw $e;
		}

		$this->db->transactionCommit();

		return true;
	}
}
