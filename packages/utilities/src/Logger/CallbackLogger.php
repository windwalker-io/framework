<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Logger;

use Psr\Log\AbstractLogger;

/**
 * @psalm-type LoggerCallback = \Closure(int|string $level, \Stringable|string $message, array $context = []): void
 */
class CallbackLogger extends AbstractLogger
{
    public function __construct(protected \Closure $callback)
    {
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        ($this->callback)($level, $message, $context);
    }
}
