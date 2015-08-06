<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Event;

/**
 * Interface DispatcherAwareInterface
 */
interface DispatcherAwareInterface
{
	/**
	 * getDispatcher
	 *
	 * @return  DispatcherInterface
	 */
	public function getDispatcher();

	/**
	 * setDispatcher
	 *
	 * @param   DispatcherInterface  $dispatcher
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDispatcher(DispatcherInterface $dispatcher);
}

