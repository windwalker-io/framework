<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Elfinder\Controller;

use Windwalker\Controller\Controller;
use Windwalker\Elfinder\View\DisplayView;

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
	 * @throws \UnexpectedValueException
	 * @return mixed
	 */
	protected function doExecute()
	{
		$config = array(
			'option' => $this->input->get('option'),
			'name'   => 'elfinder'
		);

		$view = new DisplayView(null, $this->container, $config);

		if (!($view instanceof \JView))
		{
			throw new \UnexpectedValueException(sprintf('Elfinder view: %s not found.', 'DisplayView'));
		}

		$view->getData()->config = array();

		return $view->render();
	}
}
