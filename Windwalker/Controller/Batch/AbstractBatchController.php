<?php

namespace Windwalker\Controller\Batch;

use Windwalker\Controller\Admin\AbstractListController;

/**
 * Class AbstractBatchController
 *
 * @since 1.0
 */
abstract class AbstractBatchController extends AbstractListController
{
	/**
	 * Property batch.
	 *
	 * @var array()
	 */
	protected $batch = array();

	/**
	 * Property done.
	 *
	 * @var boolean
	 */
	protected $done = false;

	/**
	 * Property categoryKey.
	 *
	 * @var string
	 */
	protected $categoryKey = 'catid';

	/**
	 * prepareExecute
	 *
	 * @return void
	 */
	protected function prepareExecute()
	{
		parent::prepareExecute();

		$this->batch = $this->input->get('batch', array(), 'array');

		unset($this->batch['task']);

		// Sanitize data.
		foreach ($this->batch as $key => &$value)
		{
			if ($value == '')
			{
				unset($this->batch[$key]);
			}
		}
	}

	/**
	 * doExecute
	 *
	 * @return bool|mixed
	 */
	protected function doExecute()
	{
		$db = $this->model->getDb();

		try
		{
			$db->transactionStart();

			$this->prepareBatch();

			$result = $this->doBatch();

			$result = $this->postBatch($result);

			$db->transactionCommit();
		}
		catch (\Exception $e)
		{
			$this->setMessage(\JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_FAILED', $e->getMessage()), 'warning');

			$db->transactionRollback();

			$this->redirectToList();

			return false;
		}

		$this->setMessage(\JText::_('JLIB_APPLICATION_SUCCESS_BATCH'));

		$this->redirectToList();

		return true;
	}

	/**
	 * doBatch
	 *
	 * @return bool
	 */
	protected function doBatch()
	{
		if (!count($this->cid))
		{
			throw new \Exception(\JText::_('JGLOBAL_NO_ITEM_SELECTED'));
		}

		$pks = array_unique($this->cid);

		$result = array();

		foreach ($pks as $pk)
		{
			if (!$pk)
			{
				continue;
			}

			$data = $this->batch;

			// Start Batch Process
			$result[] = $this->save($pk, $data);
		}

		return $result;
	}

	/**
	 * save
	 *
	 * @param int   $pk
	 * @param array $data
	 *
	 * @return mixed
	 */
	abstract protected function save($pk, $data);

	/**
	 * prepareBatch
	 *
	 * @return void
	 */
	protected function prepareBatch()
	{
		// Category Access
		if (in_array($this->categoryKey, $this->batch) && !$this->allowCategoryAdd($this->batch, $this->categoryKey))
		{
			throw new \Exception(\JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));
		}
	}

	/**
	 * postBatch
	 *
	 * @param $result
	 *
	 * @return mixed
	 */
	protected function postBatch($result)
	{
		if (!is_array($result))
		{
			$result = array($result);
		}

		if (!in_array(true, $result, true))
		{
			$this->setMessage(\JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));

			return false;
		}

		return true;
	}
}
