<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Profiler\Point;

/**
 * Class ProfilerItem
 *
 * @since 2.0
 */
class Point implements ProfilerPointInterface
{
	/**
	 * The profile point name.
	 *
	 * @var  string
	 */
	protected $name = null;

	/**
	 * The elapsed time in seconds since
	 * the first point in the profiler it belongs to was marked.
	 *
	 * @var  float
	 */
	protected $time = null;

	/**
	 * The allocated amount of memory in bytes
	 * since the first point in the profiler it belongs to was marked.
	 *
	 * @var  integer
	 */
	protected $memory = null;

	/**
	 * Constructor.
	 *
	 * @param   string   $name    The point name.
	 * @param   float    $timing  The time in seconds.
	 * @param   integer  $memory  The allocated amount of memory in bytes
	 */
	public function __construct($name, $timing = 0.0, $memory = 0)
	{
		$this->name = $name;
		$this->time = (float) $timing;
		$this->memory = (int) $memory;
	}

	/**
	 * Get the name of this profile point.
	 *
	 * @return  string  The name of this profile point.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get the elapsed time in seconds since the first
	 * point in the profiler it belongs to was marked.
	 *
	 * @return  float  The time in seconds.
	 */
	public function getTime()
	{
		return $this->time;
	}

	/**
	 * Get the allocated amount of memory in bytes
	 * since the first point in the profiler it belongs to was marked.
	 *
	 * @param bool $megaBytes
	 *
	 * @return  integer  The amount of allocated memory in B.
	 */
	public function getMemory($megaBytes = false)
	{
		return $megaBytes ? $this->memory / 1048576 : $this->memory;
	}
}
