<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Exception;

use BadMethodCallException;
use JetBrains\PhpStorm\NoReturn;
use Windwalker\Utilities\Assert\Assert;

/**
 * The ExceptionFactory class.
 */
class ExceptionFactory
{
    /**
     * badMethodCall
     *
     * @param  string       $name
     * @param  string|null  $caller
     *
     * @return BadMethodCallException
     */
    #[NoReturn]
    public static function badMethodCall(string $name, ?string $caller = null): BadMethodCallException
    {
        return new BadMethodCallException(
            sprintf(
                'Call to undefined method: %s::%s()',
                $caller ?? Assert::getCaller(2),
                $name
            )
        );
    }
}
