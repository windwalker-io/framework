<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Middleware\Test\Stub;

use Windwalker\Middleware\AbstractMiddleware;

/**
 * The StubOthelloMiddleware class.
 * 
 * @since  2.0
 */
class StubOthelloMiddleware extends AbstractMiddleware
{
	/**
	 * Call next middleware.
	 *
	 * @return  mixed
	 */
	public function call()
	{
		$r = ">>> Othello\n";

		$r .= $this->next->call();

		$r .= "<<< Othello\n";

		return $r;
	}
}
