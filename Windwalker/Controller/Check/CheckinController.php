<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\Check;

use Windwalker\Controller\Admin\AbstractListController;

/**
 * Class CheckinController
 *
 * @since 1.0
 */
class CheckinController extends AbstractListController
{
	/**
	 * doExecute
	 *
	 * @return bool|mixed
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function doExecute()
	{
		if (empty($this->cid))
		{
			throw new \InvalidArgumentException(\JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'), 500);
		}

		$pks = $this->cid;

		foreach ($pks as $i => $pk)
		{
			$this->table->reset();

			if (!$this->table->load($pk))
			{
				continue;
			}

			$data = $this->table->getProperties(true);

			if (!$this->allowEdit($data))
			{
				$this->setMessage(\JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));

				continue;
			}

			try
			{
				$this->table->checkIn($pk);
			}
			catch (\Exception $e)
			{
				$this->setMessage($this->table->getError());
			}
		}

		$message = \JText::plural($this->textPrefix . '_N_ITEMS_CHECKED_IN', count($pks));

		$this->redirectToList($message);

		return true;
	}

	/**
	 * allowEdit
	 *
	 * @param array  $data
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		return true;
	}
}
