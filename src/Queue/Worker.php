<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Queue;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Windwalker\Event\Dispatcher;
use Windwalker\Event\DispatcherAwareInterface;
use Windwalker\Event\DispatcherAwareTrait;
use Windwalker\Event\DispatcherInterface;
use Windwalker\Queue\Exception\MaxAttemptsExceededException;
use Windwalker\Queue\Job\JobInterface;
use Windwalker\Queue\Job\NullJob;
use Windwalker\Structure\Structure;

/**
 * The Worker class.
 *
 * @since  3.2
 */
class Worker implements DispatcherAwareInterface
{
    use DispatcherAwareTrait;

    const STATE_INACTIVE = 'inactive';

    const STATE_ACTIVE = 'active';

    const STATE_EXITING = 'exiting';

    const STATE_PAUSE = 'pause';

    const STATE_STOP = 'stop';

    /**
     * Property queue.
     *
     * @var  Queue
     */
    protected $manager;

    /**
     * Property logger.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Property state.
     *
     * @var  string
     */
    protected $state = self::STATE_INACTIVE;

    /**
     * Property exiting.
     *
     * @var bool
     */
    protected $exiting = false;

    /**
     * Property lastRestart.
     *
     * @var  int
     */
    protected $lastRestart;

    /**
     * Property pid.
     *
     * @var  int
     */
    protected $pid;

    /**
     * Worker constructor.
     *
     * @param Queue               $manager
     * @param DispatcherInterface $dispatcher
     * @param LoggerInterface     $logger
     */
    public function __construct(Queue $manager, DispatcherInterface $dispatcher = null, LoggerInterface $logger = null)
    {
        $this->manager    = $manager;
        $this->dispatcher = $dispatcher ?: new Dispatcher();
        $this->logger     = $logger ?: new NullLogger();
    }

    /**
     * loop
     *
     * @param string|array $queue
     * @param Structure    $options
     *
     * @return  void
     */
    public function loop($queue, Structure $options)
    {
        gc_enable();

        // Last Restart
        $this->lastRestart = (new \DateTimeImmutable('now'))->format('U');

        // Log PID
        $this->pid = getmypid();

        $this->logger->info('A worker start running... PID: ' . $this->pid);

        $this->setState(static::STATE_ACTIVE);

        while (true) {
            $this->gc();

            // @loop start
            $this->triggerEvent(
                'onWorkerLoopCycleStart', [
                'worker' => $this,
                'manager' => $this->manager,
            ]
            );

            // Timeout handler
            $this->registerSignals($options);

            if ($this->canLoop() || $options->get('force')) {
                try {
                    $this->runNextJob($queue, $options);
                } catch (\Exception $e) {
                    $msg = sprintf('Worker failure in a loop cycle: %s', $e->getMessage());

                    $this->logger->error($msg);

                    $this->triggerEvent(
                        'onWorkerLoopCycleFailure', [
                        'worker' => $this,
                        'exception' => $e,
                        'message' => $msg,
                    ]
                    );
                }
            }

            $this->stopIfNecessary($options);

            // @loop end
            $this->triggerEvent(
                'onWorkerLoopCycleEnd', [
                'worker' => $this,
                'manager' => $this->manager,
            ]
            );

            $this->sleep((int) $options->get('sleep', 1));
        }
    }

    /**
     * runNextJob
     *
     * @param string|array $queue
     * @param Structure    $options
     *
     * @return  void
     */
    public function runNextJob($queue, Structure $options)
    {
        $message = $this->getNextMessage($queue);

        if (!$message) {
            return;
        }

        $this->process($message, $options);
    }

    /**
     * process
     *
     * @param QueueMessage $message
     * @param Structure    $options
     *
     * @return  void
     */
    public function process(QueueMessage $message, Structure $options)
    {
        $maxTries = (int) $options->get('tries', 5);

        $job = $message->getJob();
        /** @var JobInterface $job */
        $job = unserialize($job);

        try {
            // @before event
            $this->triggerEvent(
                'onWorkerBeforeJobRun', [
                'worker' => $this,
                'message' => $message,
                'job' => $job,
                'manager' => $this->manager,
            ]
            );

            // Fail if max attempts
            if ($maxTries !== 0 && $maxTries < $message->getAttempts()) {
                $this->manager->delete($message);

                throw new MaxAttemptsExceededException('Max attempts exceed for Message: ' . $message->getId());
            }

            // run
            $job->execute();

            // @after event
            $this->triggerEvent(
                'onWorkerAfterJobRun', [
                'worker' => $this,
                'message' => $message,
                'job' => $job,
                'manager' => $this->manager,
            ]
            );

            $this->manager->delete($message);
        } catch (\Exception $e) {
            $this->handleJobException($job, $message, $options, $e);
        } catch (\Throwable $t) {
            $this->handleJobException($job, $message, $options, $t);
        } finally {
            if (!$message->isDeleted()) {
                $this->manager->release($message, (int) $options->get('delay', 0));
            }
        }
    }

    /**
     * canLoop
     *
     * @return  bool
     */
    protected function canLoop()
    {
        return $this->getState() === static::STATE_ACTIVE;
    }

    /**
     * registerTimeoutHandler
     *
     * @param Structure $options
     *
     * @return  void
     */
    protected function registerSignals(Structure $options)
    {
        $timeout = (int) $options->get('timeout', 60);

        if (!extension_loaded('pcntl')) {
            return;
        }

        if (version_compare(PHP_VERSION, '7.1', '>=')) {
            pcntl_async_signals(true);
        } else {
            declare (ticks=1);
        }

        if ($timeout !== 0) {
            pcntl_signal(
                SIGALRM, function () use ($timeout) {
                $this->stop('A job process over the max timeout: ' . $timeout . ' PID: ' . $this->pid);
            }
            );

            pcntl_alarm($timeout + $options->get('sleep'));
        }

        // Wait job complete then stop
        pcntl_signal(SIGINT, [$this, 'shutdown']);
        pcntl_signal(SIGTERM, [$this, 'shutdown']);
    }

    /**
     * shoutdown
     *
     * @return  void
     */
    public function shutdown()
    {
        $this->setState(static::STATE_EXITING);
    }

    /**
     * stop
     *
     * @param string $reason
     *
     * @return void
     */
    public function stop($reason = 'Unkonwn reason')
    {
        $this->logger->info('Worker stop: ' . $reason);

        $this->triggerEvent(
            'onWorkerStop', [
            'worker' => $this,
            'reason' => $reason,
        ]
        );

        $this->setState(static::STATE_STOP);

        exit();
    }

    /**
     * handleException
     *
     * @param JobInterface          $job
     * @param QueueMessage          $message
     * @param Structure             $options
     * @param \Exception|\Throwable $e
     *
     * @return void
     */
    protected function handleJobException($job, QueueMessage $message, Structure $options, $e)
    {
        if (!$job instanceof JobInterface) {
            $job = new NullJob();
        }

        $this->logger->error(
            sprintf(
                'Job [%s] (%s) failed: %s - Class: %s',
                $job->getName(),
                $message->getId(),
                $e->getMessage(),
                get_class($job)
            )
        );

        if (method_exists($job, 'failed')) {
            $job->failed($e);
        }

        $maxTries = (int) $options->get('tries', 5);

        // Delete and log error if reach max attempts.
        if ($maxTries !== 0 && $maxTries <= $message->getAttempts()) {
            $this->manager->delete($message);
            $this->logger->error(
                sprintf(
                    'Max attempts exceeded. Job: %s (%s) - Class: %s',
                    $job->getName(),
                    $message->getId(),
                    get_class($job)
                )
            );
        }

        $this->dispatcher->triggerEvent(
            'onWorkerJobFailure', [
            'worker' => $this,
            'exception' => $e,
            'job' => $job,
            'message' => $message,
        ]
        );
    }

    /**
     * getNextMessage
     *
     * @param $queue
     *
     * @return  null|QueueMessage
     */
    protected function getNextMessage($queue)
    {
        $queue = (array) $queue;

        foreach ($queue as $queueName) {
            if ($message = $this->manager->pop($queueName)) {
                return $message;
            }
        }

        return null;
    }

    /**
     * sleep
     *
     * @param int $seconds
     *
     * @return  void
     */
    protected function sleep($seconds)
    {
        usleep($seconds * 1000000);
    }

    /**
     * Method to perform basic garbage collection and memory management in the sense of clearing the
     * stat cache.  We will probably call this method pretty regularly in our main loop.
     *
     * @return  void
     */
    protected function gc()
    {
        // Perform generic garbage collection.
        gc_collect_cycles();

        // Clear the stat cache so it doesn't blow up memory.
        clearstatcache();
    }

    /**
     * Method to get property Manager
     *
     * @return  Queue
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Method to get property State
     *
     * @return  string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * setState
     *
     * @param string $state
     *
     * @return  void
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * stopIfNecessary
     *
     * @param Structure $options
     *
     * @return  void
     */
    public function stopIfNecessary(Structure $options)
    {
        $restartSignal = $options->get('restart_signal');

        if (is_file($restartSignal)) {
            $signal = file_get_contents($restartSignal);

            if ($this->lastRestart < $signal) {
                $this->stop('Receive restart signal. PID: ' . $this->pid);
            }
        }

        if ((memory_get_usage() / 1024 / 1024) >= (int) $options->get('memory_limit', 128)) {
            $this->stop('Memory usage exceeded. PID: ' . $this->pid);
        }

        if ($this->getState() === static::STATE_EXITING) {
            $this->stop('Shutdown by signal. PID: ' . $this->pid);
        }
    }
}
