<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Profiler;

use Windwalker\Profiler\Item\ProfilerItemInterface;

/**
 * Interface ProfilerInterface
 */
interface ProfilerInterface
{
	/**
	 * Get the name of this profiler.
	 *
	 * @return  string  The name of this profiler.
	 */
	public function getName();

	/**
	 * Mark a profile point.
	 *
	 * @param   string  $name  The profile point name.
	 *
	 * @return  ProfilerInterface  This method is chainable.
	 *
	 * @throws  \InvalidArgumentException  If the point already exists.
	 */
	public function mark($name);

	/**
	 * Check if the profiler has marked the given point.
	 *
	 * @param   string  $name  The name of the point.
	 *
	 * @return  boolean  True if the profiler has marked the point, false otherwise.
	 */
	public function hasItem($name);

	/**
	 * Get the point identified by the given name.
	 *
	 * @param   string  $name     The name of the point.
	 *
	 * @return  ProfilerItemInterface  The profile point or the default value.
	 */
	public function getItem($name);

	/**
	 * Get the elapsed time in seconds between the two points.
	 *
	 * @param   string  $first   The name of the first point.
	 * @param   string  $second  The name of the second point.
	 *
	 * @return  float  The elapsed time between these points in seconds.
	 *
	 * @throws  \LogicException  If the points were not marked.
	 */
	public function getTimeBetween($first, $second);

	/**
	 * Get the amount of allocated memory in bytes between the two points.
	 *
	 * @param   string  $first   The name of the first point.
	 * @param   string  $second  The name of the second point.
	 *
	 * @return  integer  The amount of allocated memory between these points in bytes.
	 *
	 * @throws  \LogicException  If the points were not marked.
	 */
	public function getMemoryBytesBetween($first, $second);
	/**
	 * Get the memory peak in bytes during the profiler run.
	 *
	 * @return  integer  The memory peak in bytes.
	 */
	public function getMemoryPeakBytes();

	/**
	 * Get the points in this profiler (from the first to the last).
	 *
	 * @return  ProfilerItemInterface[]  An array of points in this profiler.
	 */
	public function getItems();

	/**
	 * Render the profiler.
	 *
	 * @return  string  The rendered profiler.
	 */
	public function render();
}

