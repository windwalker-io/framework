<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Assert;

/**
 * The RuntimeAssert class.
 */
class RuntimeAssert extends TypeAssert
{
    protected static function exception(): callable
    {
        return static fn(string $message) => new \RuntimeException($message);
    }
}
