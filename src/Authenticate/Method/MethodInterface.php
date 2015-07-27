<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Authenticate\Method;

use Windwalker\Authenticate\Credential;

/**
 * Interface MethodInterface
 *
 * @since  2.0
 */
interface MethodInterface
{
	/**
	 * authenticate
	 *
	 * @param Credential $credential
	 *
	 * @return  integer
	 */
	public function authenticate(Credential $credential);

	/**
	 * getResult
	 *
	 * @return  integer
	 */
	public function getStatus();
}
 