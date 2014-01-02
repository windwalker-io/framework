<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\Elfinder;

use Windwalker\Controller\Controller;

/**
 * Class DisplayerController
 *
 * @since 1.0
 */
class DisplayController extends Controller
{
	/**
	 * doExecute
	 *
	 * @return mixed
	 */
	protected function doExecute()
	{
		$viewName = '\\Windwalker\\Elfinder\\View\\DisplayView';

		$view = $this->container->buildObject($viewName);

		if (!($view instanceof \JView))
		{
			throw new \UnexpectedValueException(sprintf('Elfinder view: %s not found.', $viewName));
		}

		$view->getData()->config = array();

		return $view->render();
	}
}
