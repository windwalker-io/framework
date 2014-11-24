<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Event;

/**
 * The ListenerPriority class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class ListenerPriority
{
	const MIN = -300;
	const LOW = -200;
	const BELOW_NORMAL = -100;
	const NORMAL = 0;
	const ABOVE_NORMAL = 100;
	const HIGH = 200;
	const MAX = 300;
}
