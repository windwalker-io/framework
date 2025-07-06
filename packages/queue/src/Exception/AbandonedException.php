<?php

declare(strict_types=1);

namespace Windwalker\Queue\Exception;

class AbandonedException extends \RuntimeException
{
    public static function from(\Throwable $e): static
    {
        return new static($e->getMessage(), $e->getCode(), $e);
    }

    public static function throwFrom(\Throwable $e): never
    {
        throw static::from($e);
    }

    public function toReasonText(): string
    {
        if ($this->getMessage()) {
            return 'Job abandoned, reason: ' . $this->getMessage();
        }

        return 'Job abandoned.';
    }
}
