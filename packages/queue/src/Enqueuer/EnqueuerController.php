<?php

declare(strict_types=1);

namespace Windwalker\Queue\Enqueuer;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Windwalker\Queue\Queue;

class EnqueuerController
{
    protected \Closure $invoker;

    public Queue $queue;

    public function __construct(
        public protected(set) string $channel,
        ?\Closure $invoker = null,
        public protected(set) LoggerInterface $logger = new NullLogger()
    ) {
        $this->invoker = $invoker ?? fn(self $controller, callable $invokable, array $args = []) => $invokable(
            $controller,
            ...$args
        );
    }

    public function run(callable $invokable, array $args = []): mixed
    {
        return ($this->invoker)($this, $invokable, $args);
    }

    public function log(string|array $message, string $level = LogLevel::INFO, array $context = []): static
    {
        foreach ((array) $message as $msg) {
            $this->logger->log($level, $msg, $context);
        }

        return $this;
    }

    public function enqueue(mixed $job, int $delay = 0, ?string $channel = null, array $options = []): int|string
    {
        return $this->queue->push($job, $delay, $channel ?? $this->channel, $options);
    }
}
