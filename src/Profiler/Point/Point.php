<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Profiler\Point;

/**
 * Class ProfilerItem
 *
 * @since 2.0
 */
class Point implements PointInterface
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
     * Property data.
     *
     * @var  CollectorInterface
     */
    protected $data = [];

    /**
     * Constructor.
     *
     * @param   string  $name   The point name.
     * @param   float   $timing The time in seconds.
     * @param   integer $memory The allocated amount of memory in bytes
     * @param   mixed   $data   The collector data.
     */
    public function __construct($name, $timing = 0.0, $memory = 0, $data = [])
    {
        $this->name = $name;
        $this->time = (float) $timing;
        $this->memory = (int) $memory;

        $this->setData($data);
    }

    /**
     * Create a point of current information.
     *
     * @param   string $name The point name.
     * @param   mixed  $data The collector data.
     *
     * @return  static
     */
    public static function current($name, $data = [])
    {
        return new static($name, microtime(true), memory_get_usage(false), $data);
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

    /**
     * Method to get property Data
     *
     * @return  CollectorInterface
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Method to set property data
     *
     * @param   array|CollectorInterface $data
     *
     * @return  static  Return self to support chaining.
     */
    public function setData($data)
    {
        if (!$data instanceof CollectorInterface) {
            $data = new Collector($data);
        }

        $this->data = $data;

        return $this;
    }
}
