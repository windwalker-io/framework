<?php
/**
 * Part of joomla321 project. 
 *
 * @copyright  Copyright (C) 2011 - 2013 SMS Taiwan, Inc. All rights reserved.
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
	 * @return mixed|void
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
				$this->app->enqueueMessage(\JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));

				continue;
			}

			try
			{
				$this->table->checkIn($pk);
			}
			catch (\Exception $e)
			{
				$this->app->enqueueMessage($this->table->getError());
			}
		}

		$message = $this->input->get('hmvc') ? null : \JText::plural($this->textPrefix . '_N_ITEMS_CHECKED_IN', count($pks));

		$this->redirectToList($message);

		return true;
	}
}
