<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Assert;

use LogicException;

/**
 * The LogicAssert class.
 */
class LogicAssert extends TypeAssert
{
    protected static function exception(): callable
    {
        return static fn(string $message) => new LogicException($message);
    }
}
