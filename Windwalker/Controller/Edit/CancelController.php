<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\Edit;

use Windwalker\Controller\Admin\AbstractItemController;

defined('_JEXEC') or die;

/**
 * Class CancelController
 *
 * @since 1.0
 */
class CancelController extends AbstractItemController
{
	/**
	 * Property allowReturn.
	 *
	 * @var  boolean
	 */
	protected $allowReturn = true;

	/**
	 * Generic method to cancel
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	protected function doExecute()
	{
		// Attempt to check-in the current record.
		$data = array('cid' => array($this->recordId), 'quiet' => true);

		$this->fetch($this->prefix, $this->viewList . '.check.checkin', $data);

		// Clean the session data and redirect.
		$this->releaseEditId($this->context, $this->recordId);
		$this->app->setUserState($this->context . '.data', null);

		$this->redirectToList();

		return true;

	}
}
