<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Controller\Test\Stub;

use Windwalker\Controller\AbstractMultiActionController;

/**
 * The AtubMultiActionController class.
 * 
 * @since  2.0
 */
class StubMultiActionController extends AbstractMultiActionController
{
	/**
	 * indexAction
	 *
	 * @return  mixed
	 */
	public function indexAction()
	{
		return 'index';
	}

	/**
	 * updateAction
	 *
	 * @param int    $id
	 * @param string $title
	 *
	 * @return  string
	 */
	public function updateAction($id = null, $title = '')
	{
		return 'ID: ' . $id . ' Title: ' . $title;
	}
}
