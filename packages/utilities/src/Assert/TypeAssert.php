<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Assert;

use TypeError;

/**
 * The Assert class.
 */
class TypeAssert
{
    /**
     * @template T
     *
     * @param  T              $assertion
     * @param  string         $message
     * @param  mixed          $value
     * @param  callable|null  $exception
     *
     * @return  T
     */
    public static function assert(
        mixed $assertion,
        string $message,
        mixed $value = null,
        ?callable $exception = null
    ): mixed {
        if (is_callable($assertion)) {
            $result = $assertion();
        } else {
            $result = (bool) $assertion;
        }

        if (!$result) {
            static::createAssert($exception, Assert::getCaller(2))->throwException($message, $value);
        }

        return $assertion;
    }

    public static function createAssert(?callable $exception = null, ?string $caller = null): Assert
    {
        return new Assert($exception ?? static::exception(), $caller ?? Assert::getCaller(2));
    }

    protected static function exception(): callable
    {
        return static fn(string $msg) => new TypeError($msg);
    }

    public static function describeValue($value): string
    {
        return Assert::describeValue($value);
    }
}
