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
 * @psalm-type Invoker = callable(JobController $job, callable $invokable): void
 *
 * @since  3.2
 */
class Worker extends AbstractRunner
{
    public ?Enqueuer $enqueuer = null;
    public function getRunnerName(): string
    {
        return 'Worker';
    }

    /**
     * @param  string|array  $channel
     *
     * @return  void
     */
    public function next(string|array $channel): void
    {
        $message = $this->enqueueIfAvailable($channel);

        $message ??= $this->getNextMessage($channel);

        if (!$message) {
            if ($this->options->stopWhenEmpty) {
                $this->stop('No more messages in queue.');
            }

            return;
        }

        if ($this->options->maxRuns > 0) {
            $this->runTimes++;
        }

        $this->process($message);
    }

    protected function enqueueIfAvailable(array|string $channel): ?QueueMessage
    {
        if (!$this->enqueuer) {
            return null;
        }

        $channel = (array) $channel;

        foreach ($channel as $channelName) {
            $result = $this->enqueuer->enqueue($channelName);

            if ($result && !$result instanceof QueueMessage) {
                $result = $this->queue->getMessageByJob($result);
            }

            if ($result instanceof QueueMessage) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @param  string|array  $channel
     *
     * @return  void
     *
     * @deprecated  Use next() instead.
     */
    public function runNextJob(string|array $channel): void
    {
        $this->next($channel);
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
                runner: $this,
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
                    runner: $this,
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
            if ($message->getId()) {
                $this->queue->release(
                    $message,
                    $backoff ??= $this->options->backoff
                );
            } else {
                $this->queue->push(
                    $message,
                    $backoff ??= $this->options->backoff
                );
            }
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
                runner: $this,
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
     * @param  QueueMessage  $message
     *
     * @return  JobController
     */
    public function createJonController(QueueMessage $message): JobController
    {
        if ($this->options->controllerFactory) {
            return ($this->options->controllerFactory)(
                $message,
                $this->getInvoker(),
                $this->createControllerLogger(),
            );
        }

        return $message->makeJobController(
            $this->getInvoker(),
            $this->createControllerLogger()
        );
    }

    protected function getDefaultInvoker(): \Closure
    {
        return fn(JobController $controller, callable $invokable) => $invokable($controller);
    }
}
