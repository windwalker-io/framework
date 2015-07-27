<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Authenticate\Test\Mock;

use Windwalker\Authenticate\Authenticate;
use Windwalker\Authenticate\Credential;
use Windwalker\Authenticate\Method\AbstractMethod;

/**
 * The MockMethod class.
 * 
 * @since  2.0
 */
class MockMethod extends AbstractMethod
{
	/**
	 * authenticate
	 *
	 * @param Credential $credential
	 *
	 * @return  integer
	 */
	public function authenticate(Credential $credential)
	{
		if ($credential->username == 'flower')
		{
			if ($credential->password == '1234')
			{
				$this->status = Authenticate::SUCCESS;

				return true;
			}

			$this->status = Authenticate::INVALID_CREDENTIAL;

			return false;
		}

		$this->status = Authenticate::USER_NOT_FOUND;

		return false;
	}
}
 