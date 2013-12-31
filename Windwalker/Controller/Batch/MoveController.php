<?php

namespace Windwalker\Controller\Batch;

/**
 * Class MoveController
 *
 * @since 1.0
 */
class MoveController extends AbstractBatchController
{
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
		$data[$this->urlVar] = $pk;

		if (!$this->allowEdit($data, $this->urlVar))
		{
			return false;
		}

		return $this->model->save($data);
	}
}
