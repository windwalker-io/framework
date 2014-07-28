<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Profiler\Item;


interface ProfilerItemInterface
{
	/**
	 * Get the name of this profile point.
	 *
	 * @return  string  The name of this profile point.
	 */
	public function getName();

	/**
	 * Get the elapsed time in seconds since the first
	 * point in the profiler it belongs to was marked.
	 *
	 * @return  float  The time in seconds.
	 */
	public function getTiming();

	/**
	 * Get the allocated amount of memory in bytes
	 * since the first point in the profiler it belongs to was marked.
	 *
	 * @param bool $megaBytes
	 *
	 * @return  integer  The amount of allocated memory in B.
	 */
	public function getMemory($megaBytes = false);
}
 