<?php

declare(strict_types=1);

namespace Windwalker\Queue\Job;

use Windwalker\Queue\QueueMessage;

/**
 * @psalm-type RunnerCallback = \Closure(JobController $controller): mixed
 */
// phpcs:disable
class JobController
{
    public int $attempts {
        get => $this->message->getAttempts();
    }

    public string|int $id {
        get => $this->message->getId();
    }

    public int $delay {
        get => $this->message->getDelay();
    }

    public bool $deleted {
        get => $this->message->isDeleted();
    }

    public array $body {
        get => $this->message->getBody();
    }

    public JobWrapperInterface $job {
        get => $this->message->getRawJob();
    }

    public bool $failed {
        get => $this->exception !== null;
    }

    public ?\Throwable $exception = null;

    public ?int $releaseDelay = null;

    public bool $shouldPassToNext {
        get {
            if ($this->releaseDelay !== null) {
                return true;
            }

            if ($this->deleted) {
                return true;
            }

            return false;
        }
    }

    public \Closure $runner;

    public function __construct(
        readonly public QueueMessage $message,
        ?\Closure $runner = null,
    ) {
        $this->runner = $runner ?? fn(JobController $controller, callable $handler) => $handler($controller);
    }

    public function release(int $delay = 0): void
    {
        $this->releaseDelay = $delay;
    }

    public function unrelease(): void
    {
        $this->releaseDelay = null;
    }

    public function delete(): void
    {
        $this->message->setDeleted(true);
    }

    public function undelete(): void
    {
        $this->message->setDeleted(false);
    }

    public function run(callable $handler): void
    {
        ($this->runner)($this, $handler);
    }

    public function failed(\Throwable|string $e, ?int $code = null): void
    {
        if (is_string($e)) {
            $e = new \RuntimeException($e, $code ?? 0);
        }

        $this->exception = $e;
    }

    public function success(): void
    {
        $this->exception = null;
    }
}
