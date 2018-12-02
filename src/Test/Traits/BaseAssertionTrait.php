<?php
/**
 * Part of ww4 project.
 *
 * @copyright  Copyright (C) 2019 Asikart.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Test\Traits;

use PHPUnit\Framework\TestCase;
use Windwalker\Test\Helper\TestStringHelper;

/**
 * StringTestTrait
 *
 * @since  3.2
 */
trait BaseAssertionTrait
{
    /**
     * assertStringDataEquals
     *
     * @param string $expected
     * @param string $actual
     * @param string $message
     * @param int    $delta
     * @param int    $maxDepth
     * @param bool   $canonicalize
     * @param bool   $ignoreCase
     *
     * @return  void
     */
    public static function assertStringDataEquals(
        $expected,
        $actual,
        $message = '',
        $delta = 0,
        $maxDepth = 10,
        $canonicalize = false,
        $ignoreCase = false
    ) {
        static::assertEquals(
            TestStringHelper::clean($expected),
            TestStringHelper::clean($actual),
            $message,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );
    }

    /**
     * assertStringDataEquals
     *
     * @param string $expected
     * @param string $actual
     * @param string $message
     * @param int    $delta
     * @param int    $maxDepth
     * @param bool   $canonicalize
     * @param bool   $ignoreCase
     *
     * @return  void
     */
    public static function assertStringSafeEquals(
        $expected,
        $actual,
        $message = '',
        $delta = 0,
        $maxDepth = 10,
        $canonicalize = false,
        $ignoreCase = false
    ) {
        static::assertEquals(
            trim(TestStringHelper::removeCRLF($expected)),
            trim(TestStringHelper::removeCRLF($actual)),
            $message,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );
    }

    /**
     * assertExpectedException
     *
     * @param callable      $closure
     * @param string|object $class
     * @param string        $msg
     * @param int           $code
     * @param string        $message
     *
     * @return  void
     */
    public static function assertExpectedException(
        callable $closure,
        $class = \Throwable::class,
        $msg = null,
        $code = null,
        $message = ''
    ) {
        if (is_object($class)) {
            $class = get_class($class);
        }

        try {
            $closure();
        } catch (\Exception $e) {
            static::assertInstanceOf($class, $e, $message);

            if ($msg !== null) {
                static::assertStringStartsWith($msg, $e->getMessage(), $message);
            }

            if ($code !== null) {
                static::assertEquals($code, $e->getCode(), $message);
            }

            return;
        } catch (\Throwable $t) {
            static::assertInstanceOf($class, $t, $message);

            if ($msg !== null) {
                static::assertStringStartsWith($msg, $t->getMessage(), $message);
            }

            if ($code !== null) {
                static::assertEquals($code, $t->getCode(), $message);
            }

            return;
        }

        static::fail('No exception or throwable caught.');
    }

    /**
     * expectException
     *
     * @param string $exception
     *
     * @return  mixed
     */
    public function legacyExpectException($exception)
    {
        if (method_exists(TestCase::class, 'expectException')) {
            return parent::expectException($exception);
        }

        return parent::setExpectedException($exception);
    }
}
