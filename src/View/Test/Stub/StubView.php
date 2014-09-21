<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\View\Test\Stub;

use Windwalker\View\AbstractView;

/**
 * The StubView class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class StubView extends AbstractView
{
	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @throws  \RuntimeException
	 */
	public function render()
	{
		return 'Hello ' . $this->data['foo'] . '!';
	}
}
