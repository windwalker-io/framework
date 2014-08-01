<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Profiler\Item;

/**
 * Class ProfilerItem
 *
 * @since 1.0
 */
class ProfilerItem implements ProfilerItemInterface
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
	protected $timing = null;

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
		$this->timing = (float) $timing;
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
	public function getTiming()
	{
		return $this->timing;
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

