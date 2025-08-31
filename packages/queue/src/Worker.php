<?php

declare(strict_types=1);

namespace Windwalker\Queue;

use DateTimeImmutable;
use Exception;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;
use Windwalker\Event\EventAwareInterface;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Queue\Attributes\JobBackoff;
use Windwalker\Queue\Attributes\JobFailed;
use Windwalker\Queue\Event\AfterJobRunEvent;
use Windwalker\Queue\Event\BeforeJobRunEvent;
use Windwalker\Queue\Event\DebugOutputEvent;
use Windwalker\Queue\Event\JobFailureEvent;
use Windwalker\Queue\Event\LoopEndEvent;
use Windwalker\Queue\Event\LoopFailureEvent;
use Windwalker\Queue\Event\LoopStartEvent;
use Windwalker\Queue\Event\StopEvent;
use Windwalker\Queue\Exception\MaxAttemptsExceededException;
use Windwalker\Queue\Job\JobController;

/**
 * The Worker class.
 *
 * @psalm-type Invoker = callable(JobController $job): void
 *
 * @since  3.2
 */
class Worker implements EventAwareInterface
{
    use EventAwareTrait;

    public const string STATE_INACTIVE = 'inactive';

    public const string STATE_ACTIVE = 'active';

    public const string STATE_EXITING = 'exiting';

    public const string STATE_PAUSE = 'pause';

    public const string STATE_STOP = 'stop';

    /**
     * Property state.
     *
     * @var  string
     */
    public string $state = self::STATE_INACTIVE;

    /**
     * Property lastRestart.
     *
     * @var  ?int
     */
    public ?int $lastRestart = null;

    /**
     * Property pid.
     *
     * @var  ?int
     */
    public ?int $pid = null;

    /**
     * @var ?callable
     */
    protected $invoker = null;

    public function __construct(
        protected Queue $queue,
        public WorkerOptions $options = new WorkerOptions(),
        protected LoggerInterface $logger = new NullLogger()
    ) {
    }

    /**
     * loop
     *
     * @param  string|array  $channel
     *
     * @return  void
     * @throws Exception
     */
    public function loop(string|array $channel): void
    {
        gc_enable();

        // Last Restart
        $this->lastRestart = (int) new DateTimeImmutable('now')->format('U');

        // Log PID
        $this->pid = getmypid();

        $this->logger->info('A worker start running... PID: ' . $this->pid);

        $this->setState(static::STATE_ACTIVE);

        while (!$this->isStop()) {
            $this->gc();

            $worker = $this;
            $queue = $this->queue;

            // @loop start
            $this->emit(new LoopStartEvent(worker: $worker, queue: $queue));

            // Timeout handler
            $this->registerSignals();

            if (($this->options->force ?? null) || $this->canLoop()) {
                try {
                    $this->runNextJob($channel);
                } catch (Exception $exception) {
                    $message = sprintf('Worker failure in a loop cycle: %s', $exception->getMessage());

                    $this->logger->error($message);

                    $this->emit(new LoopFailureEvent(worker: $worker, message: $message, exception: $exception));
                }
            }

            $this->stopIfNecessary();

            // @loop end
            $this->emit(new LoopEndEvent(worker: $worker, queue: $queue));

            $this->sleep((float) $this->options->sleep);
        }
    }

    /**
     * @param  string|array  $channel
     *
     * @return  void
     */
    public function runNextJob(string|array $channel): void
    {
        $message = $this->getNextMessage($channel);

        if (!$message) {
            return;
        }

        $this->process($message);
    }

    /**
     * @param  QueueMessage  $message
     *
     * @return  void
     */
    public function process(QueueMessage $message): void
    {
        $maxTries = $this->options->tries;

        $controller = $this->createJonController($message);
        $backoff = JobBackoff::fromController($controller);

        // @before event
        $event = $this->emit(
            new BeforeJobRunEvent(
                controller: $controller,
                worker: $this,
                queue: $this->queue,
            ),
        );

        $message = $event->message;
        $controller = $event->controller;

        try {
            // Fail if max attempts
            if ($backoff === false || ($maxTries !== 0 && $maxTries < $message->getAttempts())) {
                $this->queue->delete($message);

                throw new MaxAttemptsExceededException('Max attempts exceed for Message: ' . $message->getId());
            }

            $controller = $controller->run();

            if ($controller->failed) {
                throw $controller->exception;
            }

            // @after event
            $event = $this->emit(
                new AfterJobRunEvent(
                    controller: $controller,
                    worker: $this,
                    queue: $this->queue,
                ),
            );

            $controller = $event->controller;
            $this->settleJob($controller);
        } catch (Throwable $t) {
            $this->handleJobException($controller, $t, $backoff);
        }
    }

    protected function handleJobException(
        JobController $controller,
        Throwable $e,
        int|false|null $backoff = null,
    ): void {
        $controller->failed($e);

        $job = $controller->job;
        $message = $controller->message;

        $maxTries = $this->options->tries;

        // Delete and log error if reach max attempts.
        $maxAttemptsExceeds = ($backoff === false || ($maxTries !== 0 && $maxTries <= $message->getAttempts()));
        $controller->maxAttemptsExceeds = $maxAttemptsExceeds;

        // Run through JobFailed methods.
        iterator_count($controller->invokeMethodsWithAttribute(JobFailed::class));

        if ($controller->shouldDelete) {
            $this->queue->delete($message);
            $this->logger->error(
                sprintf(
                    'Job: [%s] (%s) failed. %s - Class: %s',
                    get_debug_type($job),
                    $message->getId(),
                    $maxAttemptsExceeds
                        ? 'Max attempts exceeded.'
                        : $controller->abandoned->toReasonText(),
                    get_debug_type($job),
                ),
            );

            $backoff = false;
        } else {
            $this->queue->release(
                $message,
                $backoff ??= $this->options->backoff
            );
            $this->logger->error(
                sprintf(
                    'Job: [%s] (%s) failed, will retry after %d seconds - Class: %s',
                    get_debug_type($job),
                    $message->getId(),
                    $backoff,
                    get_debug_type($job)
                ),
            );
        }

        $this->emit(
            new JobFailureEvent(
                exception: $e,
                controller: $controller,
                worker: $this,
                queue: $this->queue,
                backoff: $backoff,
            ),
        );
    }

    protected function settleJob(JobController $controller): void
    {
        if ($controller->failed) {
            throw $controller->exception;
        }

        $message = $controller->message;

        // Release job if it has a release delay.
        if ($controller->defer) {
            $message->setDeleted(false);
            $message->setDelay($controller->defer->delay);

            $this->queue->defer(
                $message,
                $controller->defer->delay
            );

            return;
        }

        $this->queue->delete($message);
    }

    /**
     * canLoop
     *
     * @return  bool
     */
    protected function canLoop(): bool
    {
        return $this->getState() === static::STATE_ACTIVE;
    }

    /**
     * @return  void
     */
    protected function registerSignals(): void
    {
        $timeout = $this->options->timeout;

        if (!extension_loaded('pcntl')) {
            return;
        }

        declare(ticks=1);

        if ($timeout !== 0) {
            pcntl_signal(
                SIGALRM,
                function () use ($timeout) {
                    $this->stop('A job process over the max timeout: ' . $timeout . ' PID: ' . $this->pid);
                },
            );

            pcntl_alarm((int) ($timeout + $this->options->sleep));
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
    public function shutdown(): void
    {
        $this->setState(static::STATE_EXITING);
    }

    /**
     * stop
     *
     * @param  string  $reason
     * @param  int     $code
     * @param  bool    $instant
     *
     * @return void
     */
    public function stop(string $reason = 'Unknown reason', int $code = 0, bool $instant = false): void
    {
        $this->logger->info('Worker stop: ' . $reason);

        $this->emit(
            new StopEvent(
                reason: $reason,
                worker: $this,
                queue: $this->queue,
            ),
        );

        $this->setState(static::STATE_STOP);

        if ($instant) {
            exit($code);
        }
    }

    public function isStop(): bool
    {
        return in_array(
            $this->getState(),
            [
                static::STATE_EXITING,
                static::STATE_STOP,
            ],
            true,
        );
    }

    /**
     * getNextMessage
     *
     * @param  string|array  $channel
     *
     * @return  null|QueueMessage
     */
    protected function getNextMessage(string|array $channel): ?QueueMessage
    {
        $channel = (array) $channel;

        foreach ($channel as $channelName) {
            if ($message = $this->queue->pop($channelName)) {
                return $message;
            }
        }

        return null;
    }

    /**
     * sleep
     *
     * @param  float  $seconds
     *
     * @return  void
     */
    protected function sleep(float $seconds): void
    {
        usleep((int) ($seconds * 1000_000));
    }

    /**
     * Method to perform basic garbage collection and memory management in the sense of clearing the
     * stat cache.  We will probably call this method pretty regularly in our main loop.
     *
     * @return  void
     */
    protected function gc(): void
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
    public function getQueue(): Queue
    {
        return $this->queue;
    }

    /**
     * Method to get property State
     *
     * @return  string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * setState
     *
     * @param  string  $state
     *
     * @return  void
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     * @return  void
     */
    public function stopIfNecessary(): void
    {
        $restartSignal = $this->options->restartSignal;

        if ($restartSignal && is_file($restartSignal)) {
            $signal = file_get_contents($restartSignal);

            if ($this->lastRestart < $signal) {
                $this->stop('Receive restart signal. PID: ' . $this->pid);
            }
        }

        if ((memory_get_usage() / 1024 / 1024) >= $this->options->memoryLimit) {
            $this->stop('Memory usage exceeded. PID: ' . $this->pid);
        }

        if ($this->getState() === static::STATE_EXITING) {
            $this->stop('Shutdown by signal. PID: ' . $this->pid);
        }
    }

    /**
     * @return Invoker
     */
    public function getInvoker(): callable
    {
        return $this->invoker ??= fn(JobController $controller, callable $invokable) => $invokable($controller);
    }

    /**
     * @param  ?Invoker  $invoker
     *
     * @return  static  Return self to support chaining.
     */
    public function setInvoker(?callable $invoker): static
    {
        $this->invoker = $invoker;

        return $this;
    }

    /**
     * @param  QueueMessage  $message
     *
     * @return  JobController
     */
    public function createJonController(QueueMessage $message): JobController
    {
        if ($this->options->jobControllerFactory) {
            return ($this->options->jobControllerFactory)(
                $message,
                $this->getInvoker(),
                $this->createJobLogger(),
            );
        }

        return $message->makeJobController(
            $this->getInvoker(),
            $this->createJobLogger()
        );
    }

    protected function createJobLogger(): LoggerInterface
    {
        return new class ($this) extends AbstractLogger {
            public function __construct(protected Worker $worker)
            {
            }

            public function log($level, \Stringable|string $message, array $context = []): void
            {
                $this->worker->emit(
                    new DebugOutputEvent(
                        level: $level,
                        message: (string) $message,
                        context: $context,
                    )
                );
            }
        };
    }
}
