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
 * Class ConnnectController
 *
 * @since 1.0
 */
class ConnectController extends Controller
{
	/**
	 * doExecute
	 *
	 * @return mixed|string
	 *
	 * @throws \UnexpectedValueException
	 */
	protected function doExecute()
	{
		$viewName = '\\Windwalker\\Elfinder\\View\\ConnectView';

		$view = $this->container->buildObject($viewName);

		if (!($view instanceof \JView))
		{
			throw new \UnexpectedValueException(sprintf('Elfinder view: %s not found.', $viewName));
		}

		$view->setConfig(array());

		return $view->render();
	}
}
 