<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise\Scheduler;

/**
 * The TaskQueue class which inspired by Guzzle.
 */
class TaskQueue
{
    /**
     * @var bool
     */
    protected ?bool $runAtShutdown = null;

    /**
     * @var callable[]
     */
    protected array $queue = [];

    /**
     * @var static
     */
    protected static $instance;

    /**
     * getInstance
     *
     * @param  TaskQueue|null  $instance
     *
     * @return  static
     */
    public static function getInstance(?self $instance = null): self
    {
        if ($instance) {
            static::$instance = $instance;
        }

        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * TaskQueue constructor.
     *
     * @param  bool  $runAtShutdown
     */
    public function __construct(bool $runAtShutdown = true)
    {
        $this->runAtShutdown = $runAtShutdown;

        if ($runAtShutdown) {
            register_shutdown_function(
                function () {
                    if ($this->runAtShutdown) {
                        // Only run the tasks if an E_ERROR didn't occur.
                        $err = error_get_last();

                        if (!$err || ($err['type'] ^ E_ERROR)) {
                            $this->run();
                        }
                    }
                }
            );
        }
    }

    /**
     * disableShutdownRunner
     *
     * @return  void
     */
    public function disableShutdownRunner(): void
    {
        $this->runAtShutdown = false;
    }

    /**
     * push
     *
     * @param  callable  $task
     *
     * @return  void
     */
    public function push(callable $task): void
    {
        $this->queue[] = $task;
    }

    /**
     * run
     *
     * @return  void
     */
    public function run(): void
    {
        while ($task = array_shift($this->queue)) {
            $task();
        }
    }
}
