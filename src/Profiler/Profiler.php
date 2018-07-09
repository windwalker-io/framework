<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Profiler;

use Windwalker\Profiler\Point\CollectorInterface;
use Windwalker\Profiler\Point\Point;
use Windwalker\Profiler\Point\PointInterface;
use Windwalker\Profiler\Renderer\DefaultRenderer;
use Windwalker\Profiler\Renderer\ProfilerRendererInterface;

/**
 * Class Profiler.
 *
 * @since 2.0
 */
class Profiler implements ProfilerInterface, \Countable
{
    /**
     * The name of the profiler.
     *
     * @var string
     */
    protected $name = '';

    /**
     * A lookup array containing the
     * names of the already marked points as keys
     * and their indexes in $points as value.
     * It is used to quickly find a point
     * without having to traverse $points.
     *
     * @var PointInterface[]
     */
    protected $points = [];

    /**
     * A flag to see if we must get
     * the real memory usage, or the usage of emalloc().
     *
     * @var bool
     */
    protected $memoryRealUsage;

    /**
     * The timestamp with microseconds
     * when the first point was marked.
     *
     * @var float
     */
    protected $startTimeStamp = 0.0;

    /**
     * The memory usage in bytes
     * when the first point was marked.
     *
     * @var int
     */
    protected $startMemoryBytes = 0;

    /**
     * The memory peak in bytes during
     * the profiler run.
     *
     * @var int
     */
    protected $memoryPeakBytes;

    /**
     * The profiler renderer.
     *
     * @var ProfilerRendererInterface
     */
    protected $renderer;

    /**
     * Constructor.
     *
     * @param string                    $name            The profiler name.
     * @param ProfilerRendererInterface $renderer        The renderer.
     * @param PointInterface[]          $points          An array of profile points.
     * @param bool                      $memoryRealUsage True to get the real memory usage.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $name,
        ProfilerRendererInterface $renderer = null,
        array $points = [],
        $memoryRealUsage = null
    ) {
        $this->name = $name;
        $this->renderer = $renderer ?: new DefaultRenderer();

        if (empty($points)) {
            $this->points = [];
        } else {
            $this->setPoints($points);
        }

        // HHVM has different behavior on memory_get_usage()
        $this->memoryRealUsage = ($memoryRealUsage === null) ? defined('HHVM_VERSION') : (bool) $memoryRealUsage;
    }

    /**
     * set Point.
     *
     * @param PointInterface $point
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public function setPoint(PointInterface $point)
    {
        if (isset($this->points[$point->getName()])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The point %s already exists in the profiler %s.',
                    $point->getName(),
                    $this->name
                )
            );
        }

        // Add it in the lookup table.
        $this->points[$point->getName()] = $point;

        return $this;
    }

    /**
     * Set the points in this profiler.
     * This function is called by the constructor when injecting an array of points
     * (mostly for testing purposes).
     *
     * @param PointInterface[] $points An array of profile points.
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    protected function setPoints(array $points)
    {
        foreach ($points as $point) {
            $this->setPoint($point);
        }
    }

    /**
     * Get the name of this profiler.
     *
     * @return string The name of this profiler.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Mark a profile point.
     *
     * @param string                   $name The profile point name.
     * @param array|CollectorInterface $data The data collection of this point.
     *
     * @throws \InvalidArgumentException If the point already exists.
     *
     * @return ProfilerInterface This method is chainable.
     */
    public function mark($name, $data = [])
    {
        // If a point already exists with this name.
        if (isset($this->points[$name])) {
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
        if (empty($this->points)) {
            $this->startTimeStamp = $timeStamp;
            $this->startMemoryBytes = $memoryBytes;
        }

        // Create the point.
        $point = new Point(
            $name,
            $timeStamp - $this->startTimeStamp,
            $memoryBytes - $this->startMemoryBytes,
            $data
        );

        // Store it.
        $this->setPoint($point);

        return $this;
    }

    /**
     * Check if the profiler has marked the given point.
     *
     * @param string $name The name of the point.
     *
     * @return bool True if the profiler has marked the point, false otherwise.
     */
    public function hasPoint($name)
    {
        return isset($this->points[$name]);
    }

    /**
     * Get the point identified by the given name.
     *
     * @param string $name The name of the point.
     *
     * @return PointInterface|mixed The profile point or the default value.
     */
    public function getPoint($name)
    {
        if (isset($this->points[$name])) {
            return $this->points[$name];
        }
    }

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
    public function getTimeBetween($first, $second)
    {
        if (!isset($this->points[$first])) {
            throw new \LogicException(sprintf('The point %s was not marked in the profiler %s.', $first, $this->name));
        }

        if (!isset($this->points[$second])) {
            throw new \LogicException(sprintf('The point %s was not marked in the profiler %s.', $second, $this->name));
        }

        $firstPoint = $this->points[$first];
        $secondPoint = $this->points[$second];

        return abs($secondPoint->getTime() - $firstPoint->getTime());
    }

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
    public function getMemoryBetween($first, $second)
    {
        if (!isset($this->points[$first])) {
            throw new \LogicException(sprintf('The point %s was not marked in the profiler %s.', $first, $this->name));
        }

        if (!isset($this->points[$second])) {
            throw new \LogicException(sprintf('The point %s was not marked in the profiler %s.', $second, $this->name));
        }

        $firstPoint = $this->points[$first];
        $secondPoint = $this->points[$second];

        return abs($secondPoint->getMemory() - $firstPoint->getMemory());
    }

    /**
     * Get the memory peak in bytes during the profiler run.
     *
     * @return int The memory peak in bytes.
     */
    public function getMemoryPeakBytes()
    {
        return $this->memoryPeakBytes;
    }

    /**
     * Method to get property MemoryRealUsage.
     *
     * @return bool
     */
    public function getMemoryRealUsage()
    {
        return $this->memoryRealUsage;
    }

    /**
     * Get the points in this profiler (from the first to the last).
     *
     * @return PointInterface[] An array of points in this profiler.
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set the renderer to render this profiler.
     *
     * @param ProfilerRendererInterface $renderer The renderer.
     *
     * @return Profiler This method is chainable.
     */
    public function setRenderer(ProfilerRendererInterface $renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Get the currently used renderer in this profiler.
     *
     * @return ProfilerRendererInterface The renderer.
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Render the profiler.
     *
     * @return string The rendered profiler.
     */
    public function render()
    {
        return $this->renderer->render($this);
    }

    /**
     * Cast the profiler to a string using the renderer.
     *
     * @return string The rendered profiler.
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Get an iterator on the profiler points.
     *
     * @return \ArrayIterator An iterator on the profiler points.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->points);
    }

    /**
     * Count the number of points in this profiler.
     *
     * @return int The number of points.
     */
    public function count()
    {
        return count($this->points);
    }

    /**
     * Method to set property memoryRealUsage.
     *
     * @param bool $memoryRealUsage
     *
     * @return static Return self to support chaining.
     */
    public function useMemoryRealUsage($memoryRealUsage)
    {
        $this->memoryRealUsage = $memoryRealUsage;

        return $this;
    }
}
