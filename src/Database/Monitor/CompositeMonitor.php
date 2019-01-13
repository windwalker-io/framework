<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Monitor;

/**
 * The CompositeMonitor class.
 *
 * @since  3.5
 */
class CompositeMonitor implements QueryMonitorInterface
{
    /**
     * Property monitors.
     *
     * @var  QueryMonitorInterface[]
     */
    protected $monitors = [];

    /**
     * Start a query monitor.
     *
     * @param string $query The SQL to be executed.
     *
     * @return  void
     *
     * @since  3.5
     */
    public function start(string $query): void
    {
        foreach ($this->monitors as $monitor) {
            $monitor->start($query);
        }
    }

    /**
     * Stop query monitor.
     *
     * @return  void
     *
     * @since  3.5
     */
    public function stop(): void
    {
        foreach ($this->monitors as $monitor) {
            $monitor->stop();
        }
    }

    /**
     * addMonitor
     *
     * @param QueryMonitorInterface $monitor
     *
     * @return  static
     *
     * @since  3.5
     */
    public function addMonitor(QueryMonitorInterface $monitor): self
    {
        $this->monitors[] = $monitor;

        return $this;
    }

    /**
     * reset
     *
     * @return  static
     *
     * @since  3.5
     */
    public function reset(): self
    {
        $this->monitors = [];

        return $this;
    }

    /**
     * Method to get property Monitors
     *
     * @return  QueryMonitorInterface[]
     *
     * @since  3.5
     */
    public function getMonitors(): array
    {
        return $this->monitors;
    }

    /**
     * Method to set property monitors
     *
     * @param   QueryMonitorInterface[] $monitors
     *
     * @return  static  Return self to support chaining.
     *
     * @since  3.5
     */
    public function setMonitors(array $monitors): self
    {
        $this->monitors = $monitors;

        return $this;
    }
}
