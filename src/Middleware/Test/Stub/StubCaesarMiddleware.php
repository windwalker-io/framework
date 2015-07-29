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
 * The StubCaesarMiddleware class.
 * 
 * @since  2.0
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
