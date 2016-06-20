<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Authorisation;

/**
 * The PolicyInterface class.
 *
 * @since  {DEPLOY_VERSION}
 */
interface PolicyInterface
{
	/**
	 * authorise
	 *
	 * @param   mixed  $user
	 * @param   mixed  $data
	 *
	 * @return  boolean
	 */
	public function authorise($user, $data = null);
}
