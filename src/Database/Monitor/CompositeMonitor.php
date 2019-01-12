<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Database\Monitor;

/**
 * The CompositeMonitor class.
 *
 * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
     */
    public function setMonitors(array $monitors): self
    {
        $this->monitors = $monitors;

        return $this;
    }
}
