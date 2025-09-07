<?php

declare(strict_types=1);

namespace Windwalker\Queue\Enqueuer;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

class EnqueuerController
{
    protected \Closure $invoker;

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

    public function log(string|array $message, string $level = LogLevel::DEBUG, array $context = []): static
    {
        foreach ((array) $message as $msg) {
            $this->logger->log($level, $msg, $context);
        }

        return $this;
    }
}
