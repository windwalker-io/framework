<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\View\Test\Stub;

use Windwalker\View\AbstractView;

/**
 * The StubView class.
 * 
 * @since  2.0
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
