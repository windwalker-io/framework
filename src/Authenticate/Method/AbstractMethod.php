<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Authenticate\Method;

/**
 * The AbstractMethod class.
 * 
 * @since  2.0
 */
abstract class AbstractMethod implements MethodInterface
{
	/**
	 * Property status.
	 *
	 * @var integer
	 */
	protected $status;

	/**
	 * getStatus
	 *
	 * @return  integer
	 */
	public function getStatus()
	{
		return $this->status;
	}
}
 