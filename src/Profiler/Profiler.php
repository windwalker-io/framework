<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Profiler;

use Windwalker\Profiler\Item\ProfilerItem;
use Windwalker\Profiler\Item\ProfilerItemInterface;
use Windwalker\Profiler\Renderer\DefaultRenderer;
use Windwalker\Profiler\Renderer\ProfilerRendererInterface;

/**
 * Class Profiler
 *
 * @since 1.0
 */
class Profiler implements ProfilerInterface
{
	/**
	 * The name of the profiler.
	 *
	 * @var  string
	 */
	protected $name = '';

	/**
	 * A lookup array containing the
	 * names of the already marked points as keys
	 * and their indexes in $points as value.
	 * It is used to quickly find a point
	 * without having to traverse $points.
	 *
	 * @var  ProfilerItemInterface[]
	 */
	protected $items = array();

	/**
	 * A flag to see if we must get
	 * the real memory usage, or the usage of emalloc().
	 *
	 * @var  boolean
	 */
	protected $memoryRealUsage;

	/**
	 * The timestamp with microseconds
	 * when the first point was marked.
	 *
	 * @var  float
	 */
	protected $startTimeStamp = 0.0;

	/**
	 * The memory usage in bytes
	 * when the first point was marked.
	 *
	 * @var  integer
	 */
	protected $startMemoryBytes = 0;

	/**
	 * The memory peak in bytes during
	 * the profiler run.
	 *
	 * @var  integer
	 */
	protected $memoryPeakBytes;

	/**
	 * The profiler renderer.
	 *
	 * @var  ProfilerRendererInterface
	 */
	protected $renderer;

	/**
	 * Constructor.
	 *
	 * @param   string                     $name             The profiler name.
	 * @param   ProfilerRendererInterface  $renderer         The renderer.
	 * @param   ProfilerItemInterface[]    $items            An array of profile points.
	 * @param   boolean                    $memoryRealUsage  True to get the real memory usage.
	 *
	 * @throws  \InvalidArgumentException
	 */
	public function __construct($name, ProfilerRendererInterface $renderer = null, array $items = array(), $memoryRealUsage = false)
	{
		$this->name = $name;
		$this->renderer = $renderer ? : new DefaultRenderer;

		if (empty($items))
		{
			$this->items = array();
		}

		else
		{
			$this->setItems($items);
		}

		$this->memoryRealUsage = (bool) $memoryRealUsage;
	}

	/**
	 * setItem
	 *
	 * @param ProfilerItemInterface $item
	 *
	 * @return  static
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function setItem(ProfilerItemInterface $item)
	{
		if (!($item instanceof ProfilerItemInterface))
		{
			throw new \InvalidArgumentException('One of the passed point does not implement ProfilerItemInterface.');
		}

		if (isset($this->items[$item->getName()]))
		{
			throw new \InvalidArgumentException(
				sprintf(
					'The item %s already exists in the profiler %s.',
					$item->getName(),
					$this->name
				)
			);
		}

		// Add it in the lookup table.
		$this->items[$item->getName()] = $item;

		return $this;
	}

	/**
	 * Set the points in this profiler.
	 * This function is called by the constructor when injecting an array of points
	 * (mostly for testing purposes).
	 *
	 * @param   ProfilerItemInterface[]  $items  An array of profile points.
	 *
	 * @return  void
	 *
	 * @throws  \InvalidArgumentException
	 */
	protected function setItems(array $items)
	{
		foreach ($items as $item)
		{
			$this->setItem($item);
		}
	}

	/**
	 * Get the name of this profiler.
	 *
	 * @return  string  The name of this profiler.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Mark a profile point.
	 *
	 * @param   string  $name  The profile point name.
	 *
	 * @return  ProfilerInterface  This method is chainable.
	 *
	 * @throws  \InvalidArgumentException  If the point already exists.
	 */
	public function mark($name)
	{
		// If a point already exists with this name.
		if (isset($this->items[$name]))
		{
			throw new \InvalidArgumentException(
				sprintf(
					'A point already exists with the name %s in the profiler %s.',
					$name,
					$this->name
				)
			);
		}

		// Update the memory peak (it cannot decrease).
		$this->memoryPeakBytes = memory_get_peak_usage($this->memoryRealUsage);

		// Get the current timestamp and allocated memory amount.
		$timeStamp = microtime(true);
		$memoryBytes = memory_get_usage($this->memoryRealUsage);

		// If this is the first point.
		if (empty($this->items))
		{
			$this->startTimeStamp = $timeStamp;
			$this->startMemoryBytes = $memoryBytes;
		}

		// Create the point.
		$item = new ProfilerItem(
			$name,
			$timeStamp - $this->startTimeStamp,
			$memoryBytes - $this->startMemoryBytes
		);

		// Store it.
		$this->setItem($item);

		return $this;
	}

	/**
	 * Check if the profiler has marked the given point.
	 *
	 * @param   string  $name  The name of the point.
	 *
	 * @return  boolean  True if the profiler has marked the point, false otherwise.
	 */
	public function hasItem($name)
	{
		return isset($this->items[$name]);
	}

	/**
	 * Get the point identified by the given name.
	 *
	 * @param   string  $name     The name of the point.
	 *
	 * @return  ProfilerItemInterface|mixed  The profile point or the default value.
	 */
	public function getItem($name)
	{
		if (isset($this->items[$name]))
		{
			return $this->items[$name];
		}

		return null;
	}

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
	public function getTimeBetween($first, $second)
	{
		if (!isset($this->items[$first]))
		{
			throw new \LogicException(sprintf('The point %s was not marked in the profiler %s.', $first, $this->name));
		}

		if (!isset($this->items[$second]))
		{
			throw new \LogicException(sprintf('The point %s was not marked in the profiler %s.', $second, $this->name));
		}

		$firstItem = $this->items[$first];
		$secondItem = $this->items[$second];

		return abs($secondItem->getTiming() - $firstItem->getTiming());
	}

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
	public function getMemoryBytesBetween($first, $second)
	{
		if (!isset($this->items[$first]))
		{
			throw new \LogicException(sprintf('The point %s was not marked in the profiler %s.', $first, $this->name));
		}

		if (!isset($this->items[$second]))
		{
			throw new \LogicException(sprintf('The point %s was not marked in the profiler %s.', $second, $this->name));
		}

		$firstItem = $this->items[$first];
		$secondItem = $this->items[$second];

		return abs($secondItem->getMemory() - $firstItem->getMemory());
	}

	/**
	 * Get the memory peak in bytes during the profiler run.
	 *
	 * @return  integer  The memory peak in bytes.
	 */
	public function getMemoryPeakBytes()
	{
		return $this->memoryPeakBytes;
	}

	/**
	 * Get the points in this profiler (from the first to the last).
	 *
	 * @return  ProfilerItemInterface[]  An array of points in this profiler.
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * Set the renderer to render this profiler.
	 *
	 * @param   ProfilerRendererInterface  $renderer  The renderer.
	 *
	 * @return  Profiler  This method is chainable.
	 */
	public function setRenderer(ProfilerRendererInterface $renderer)
	{
		$this->renderer = $renderer;

		return $this;
	}

	/**
	 * Get the currently used renderer in this profiler.
	 *
	 * @return  ProfilerRendererInterface  The renderer.
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}

	/**
	 * Render the profiler.
	 *
	 * @return  string  The rendered profiler.
	 */
	public function render()
	{
		return $this->renderer->render($this);
	}

	/**
	 * Cast the profiler to a string using the renderer.
	 *
	 * @return  string  The rendered profiler.
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Get an iterator on the profiler points.
	 *
	 * @return  \ArrayIterator  An iterator on the profiler points.
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->items);
	}

	/**
	 * Count the number of points in this profiler.
	 *
	 * @return  integer  The number of points.
	 */
	public function count()
	{
		return count($this->items);
	}
}
 