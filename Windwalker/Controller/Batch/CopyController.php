<?php

namespace Windwalker\Controller\Batch;

/**
 * Class MoveController
 *
 * @since 1.0
 */
class CopyController extends AbstractBatchController
{
	/**
	 * Property incrementFields.
	 *
	 * @var array
	 */
	protected $incrementFields = array(
		'title' => 'default',
		'alias' => 'dash'
	);

	/**
	 * save
	 *
	 * @param int   $pk
	 * @param array $data
	 *
	 * @return bool|mixed
	 */
	protected function save($pk, $data)
	{
		if (!$this->allowAdd($data, $this->urlVar))
		{
			return false;
		}

		// We load existing item first and bind data into it.
		$this->table->reset();

		$this->table->load($pk);

		$this->table->bind($data);

		// Dump as array
		$item = $this->table->getProperties(true);

		// Handle Title increment
		$table2 = $this->model->getTable();

		$condition = array();

		// Check table has increment fields, default is title and alias.
		foreach ($this->incrementFields as $field => $type)
		{
			if (property_exists($this->table, $field))
			{
				$condition[$field] = $item[$field];
			}
		}

		// Recheck item with same conditions(default is title & alias), if true, increment them.
		// If no item got, means it is the max number.
		while ($table2->load($condition))
		{
			foreach ($this->incrementFields as $field => $type)
			{
				if (property_exists($this->table, $field))
				{
					$item[$field] = $condition[$field] = \JString::increment($item[$field], $type);
				}
			}
		}

		// Unset the primary key so that we can copy it.
		unset($item[$this->urlVar]);

		return $this->model->save($item);
	}
}
