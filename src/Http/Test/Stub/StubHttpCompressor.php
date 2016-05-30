<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Test\Stub;

use Windwalker\Http\Output\HttpCompressor;

/**
 * The StubHttpCompressor class.
 *
 * @since  {DEPLOY_VERSION}
 */
class StubHttpCompressor extends HttpCompressor
{
	/**
	 * checkConnectionAlive
	 *
	 * @return  bool
	 */
	public function checkConnectionAlive()
	{
		return true;
	}

	/**
	 * checkHeadersSent
	 *
	 * @return  bool
	 */
	public function checkHeadersSent()
	{
		return false;
	}
}
