<?php

declare(strict_types=1);

namespace Windwalker\Queue\Exception;

class DeferredException extends \RuntimeException
{
    public function __construct(public int $delay, string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function from(int $delay, \Throwable $e): static
    {
        return new static($delay, $e->getMessage(), $e->getCode(), $e);
    }

    public static function throwFrom(int $delay, \Throwable $e): never
    {
        throw static::from($delay, $e);
    }

    public function getReasonText(): string
    {
        if ($this->getMessage()) {
            return 'released after ' . $this->delay . ' seconds, reason: ' . $this->getMessage();
        }

        return 'released after ' . $this->delay . ' seconds.';
    }
}
