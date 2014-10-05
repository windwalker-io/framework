<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Middleware\Test\Stub;

use Windwalker\Middleware\AbstractMiddleware;

/**
 * The StubOthelloMiddleware class.
 * 
 * @since  {DEPLOY_VERSION}
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
