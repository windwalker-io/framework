<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Profiler;

use Windwalker\Profiler\Point\PointInterface;
use Windwalker\Profiler\Point\ProfilerPointInterface;

/**
 * Interface ProfilerInterface.
 */
interface ProfilerInterface
{
    /**
     * Get the name of this profiler.
     *
     * @return string The name of this profiler.
     */
    public function getName();

    /**
     * Mark a profile point.
     *
     * @param string $name The profile point name.
     *
     * @throws \InvalidArgumentException If the point already exists.
     *
     * @return ProfilerInterface This method is chainable.
     */
    public function mark($name);

    /**
     * Check if the profiler has marked the given point.
     *
     * @param string $name The name of the point.
     *
     * @return bool True if the profiler has marked the point, false otherwise.
     */
    public function hasPoint($name);

    /**
     * Get the point identified by the given name.
     *
     * @param string $name The name of the point.
     *
     * @return ProfilerPointInterface The profile point or the default value.
     */
    public function getPoint($name);

    /**
     * Get the elapsed time in seconds between the two points.
     *
     * @param string $first  The name of the first point.
     * @param string $second The name of the second point.
     *
     * @throws \LogicException If the points were not marked.
     *
     * @return float The elapsed time between these points in seconds.
     */
    public function getTimeBetween($first, $second);

    /**
     * Get the amount of allocated memory in bytes between the two points.
     *
     * @param string $first  The name of the first point.
     * @param string $second The name of the second point.
     *
     * @throws \LogicException If the points were not marked.
     *
     * @return int The amount of allocated memory between these points in bytes.
     */
    public function getMemoryBetween($first, $second);

    /**
     * Get the memory peak in bytes during the profiler run.
     *
     * @return int The memory peak in bytes.
     */
    public function getMemoryPeakBytes();

    /**
     * Get the points in this profiler (from the first to the last).
     *
     * @return PointInterface[] An array of points in this profiler.
     */
    public function getPoints();

    /**
     * Render the profiler.
     *
     * @return string The rendered profiler.
     */
    public function render();
}
