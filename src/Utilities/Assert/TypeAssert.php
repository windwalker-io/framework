<?php

/**
 * Part of ww4 project.
 *
 * @copyright  Copyright (C) 2019 __ORGANIZATION__.
 * @license    LGPL-2.0-or-later
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Assert;

use TypeError;

/**
 * The Assert class.
 *
 * @since  3.5.17
 */
class TypeAssert
{
    /**
     * @var  string
     */
    protected static $exceptionClass = TypeError::class;

    /**
     * @var int
     */
    protected static $exceptionCode = 500;

    /**
     * assert
     *
     * @param  bool|callable  $assertion
     * @param  string         $message
     * @param  mixed          $value
     * @param  string|null    $caller
     *
     * @return  void
     *
     * @throws TypeError
     *
     * @since  3.5.17
     */
    public static function assert($assertion, string $message, $value = null, ?string $caller = null): void
    {
        if (is_callable($assertion)) {
            $result = $assertion();
        } else {
            $result = (bool) $assertion;
        }

        if (!$result) {
            $caller = $caller ?? static::getCaller();

            static::throwException($message, $value, $caller);
        }
    }

    public static function invalidArguments(string $message, $value = null, ?string $caller = null): void
    {
        $caller = $caller ?? static::getCaller();

        static::throwException($message, $value, $caller);
    }

    public static function throwException(string $message, $value = null, ?string $caller = null): void
    {
        $caller = $caller ?? static::getCaller();

        throw static::exception($message, $value, $caller);
    }

    public static function exception(string $message, $value = null, ?string $caller = null)
    {
        $caller = $caller ?? static::getCaller();

        $class = static::$exceptionClass;

        return new $class(sprintf($message, $caller, static::describeValue($value)), static::$exceptionCode);
    }

    public static function getCaller(int $backSteps = 2): string
    {
        $trace = debug_backtrace()[$backSteps];

        return trim(($trace['class'] ?? '') . '::' . ($trace['function']), ':') . '()';
    }

    public static function describeValue($value): string
    {
        if ($value === null) {
            return '(NULL)';
        }

        if ($value === true) {
            return 'BOOL (TRUE)';
        }

        if ($value === false) {
            return 'BOOL (FALSE)';
        }

        if (is_object($value)) {
            return get_class($value);
        }

        if (is_array($value)) {
            return 'array';
        }

        if (is_string($value)) {
            return sprintf('string(%s) "%s"', strlen($value), $value);
        }

        if (is_numeric($value)) {
            return sprintf('%s(%s)', gettype($value), $value);
        }

        return (string) $value;
    }
}
