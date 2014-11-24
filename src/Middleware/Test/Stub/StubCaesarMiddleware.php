<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Middleware\Test\Stub;

use Windwalker\Middleware\AbstractMiddleware;

/**
 * The StubCaesarMiddleware class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class StubCaesarMiddleware extends AbstractMiddleware
{
	/**
	 * Call next middleware.
	 *
	 * @return  mixed
	 */
	public function call()
	{
		$r = ">>> Caesar\n";

		$r .= $this->next->call();

		$r .= "<<< Caesar\n";

		return $r;
	}
}
