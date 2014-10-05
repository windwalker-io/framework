<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Event;

/**
 * The ListenerPriority class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class ListenerPriority
{
	const MIN = -3;
	const LOW = -2;
	const BELOW_NORMAL = -1;
	const NORMAL = 0;
	const ABOVE_NORMAL = 1;
	const HIGH = 2;
	const MAX = 3;
}
