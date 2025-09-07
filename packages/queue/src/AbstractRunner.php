<?php

declare(strict_types=1);

namespace Windwalker\Queue;

use DateTimeImmutable;
use Exception;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Windwalker\Event\EventAwareInterface;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Queue\Event\DebugOutputEvent;
use Windwalker\Queue\Event\LoopEndEvent;
use Windwalker\Queue\Event\LoopFailureEvent;
use Windwalker\Queue\Event\LoopStartEvent;
use Windwalker\Queue\Event\StopEvent;

abstract class AbstractRunner implements EventAwareInterface
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
        public RunnerOptions $options = new RunnerOptions(),
        protected LoggerInterface $logger = new NullLogger()
    ) {
    }

    abstract public function getRunnerName(): string;

    /**
     * @param  string|array  $channel
     *
     * @return  void
     */
    abstract public function next(string|array $channel): void;

    /**
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
            $this->emit(new LoopStartEvent(runner: $worker, queue: $queue));

            // Timeout handler
            $this->registerSignals();

            if (($this->options->force ?? null) || $this->canLoop()) {
                try {
                    $this->next($channel);
                } catch (Exception $exception) {
                    $message = sprintf('Worker failure in a loop cycle: %s', $exception->getMessage());

                    $this->logger->error($message);

                    $this->emit(new LoopFailureEvent(runner: $worker, message: $message, exception: $exception));
                }
            }

            $this->stopIfNecessary();

            // @loop end
            $this->emit(new LoopEndEvent(runner: $worker, queue: $queue));

            $this->sleep((float) $this->options->sleep);
        }
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
                    $this->stop('A loop process over the max timeout: ' . $timeout . ' PID: ' . $this->pid);
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
                runner: $this,
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

    public function getInvoker(): callable
    {
        return $this->invoker ??= $this->getDefaultInvoker();
    }

    /**
     * @param  ?callable  $invoker
     *
     * @return  static  Return self to support chaining.
     */
    public function setInvoker(?callable $invoker): static
    {
        $this->invoker = $invoker;

        return $this;
    }

    abstract protected function getDefaultInvoker(): \Closure;

    protected function createControllerLogger(): LoggerInterface
    {
        return new class ($this) extends AbstractLogger {
            public function __construct(protected AbstractRunner $runner)
            {
            }

            public function log($level, \Stringable|string $message, array $context = []): void
            {
                $this->runner->emit(
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
