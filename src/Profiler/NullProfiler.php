<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Profiler;

use Windwalker\Profiler\Point\PointInterface;

/**
 * The NullProfiler class.
 *
 * @since  2.1.1
 */
class NullProfiler implements ProfilerInterface
{
    /**
     * Mark a profile point.
     *
     * @param   string $name The profile point name.
     *
     * @return  ProfilerInterface  This method is chainable.
     *
     * @throws  \InvalidArgumentException  If the point already exists.
     */
    public function mark($name)
    {
        return $this;
    }

    /**
     * Check if the profiler has marked the given point.
     *
     * @param   string $name The name of the point.
     *
     * @return  boolean  True if the profiler has marked the point, false otherwise.
     */
    public function hasPoint($name)
    {
        return false;
    }

    /**
     * Get the point identified by the given name.
     *
     * @param   string $name The name of the point.
     *
     * @return  PointInterface  The profile point or the default value.
     */
    public function getPoint($name)
    {
        return null;
    }

    /**
     * Get the elapsed time in seconds between the two points.
     *
     * @param   string $first  The name of the first point.
     * @param   string $second The name of the second point.
     *
     * @return  float  The elapsed time between these points in seconds.
     *
     * @throws  \LogicException  If the points were not marked.
     */
    public function getTimeBetween($first, $second)
    {
        return 0;
    }

    /**
     * Get the amount of allocated memory in bytes between the two points.
     *
     * @param   string $first  The name of the first point.
     * @param   string $second The name of the second point.
     *
     * @return  integer  The amount of allocated memory between these points in bytes.
     *
     * @throws  \LogicException  If the points were not marked.
     */
    public function getMemoryBetween($first, $second)
    {
        return 0;
    }

    /**
     * Get the memory peak in bytes during the profiler run.
     *
     * @return  integer  The memory peak in bytes.
     */
    public function getMemoryPeakBytes()
    {
        return 0;
    }

    /**
     * Get the points in this profiler (from the first to the last).
     *
     * @return  PointInterface[]  An array of points in this profiler.
     */
    public function getPoints()
    {
        return [];
    }

    /**
     * Render the profiler.
     *
     * @return  string  The rendered profiler.
     */
    public function render()
    {
        return '';
    }

    /**
     * Get the name of this profiler.
     *
     * @return  string  The name of this profiler.
     */
    public function getName()
    {
        return null;
    }
}
