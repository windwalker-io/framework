<?php

declare(strict_types=1);

namespace Windwalker\Queue\Exception;

class UnrecoverableException extends \RuntimeException
{
    public static function from(\Throwable $e): static
    {
        return new static($e->getMessage(), (int) $e->getCode(), $e);
    }

    public static function throwFrom(\Throwable $e): never
    {
        throw static::from($e);
    }
}
