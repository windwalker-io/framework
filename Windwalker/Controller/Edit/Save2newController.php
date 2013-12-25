<?php

namespace Windwalker\Controller\Edit;

use Windwalker\Controller\Admin\AbstractItemController;
use Windwalker\Model\Exception\ValidateFailException;

/**
 * Class SaveController
 *
 * @since 1.0
 */
class Save2newController extends SaveController
{
	/**
	 * postExecute
	 *
	 * @param null $return
	 *
	 * @return null
	 */
	protected function postExecute($return = null)
	{
		// Clear the record id and data from the session.
		$this->releaseEditId($this->context, $this->recordId);
		$this->app->setUserState($this->context . '.data', null);

		// Redirect back to the edit screen.
		$this->app->redirect(\JRoute::_($this->getRedirectItemUrl(null, $this->urlVar), false));

		return $return;
	}
}
