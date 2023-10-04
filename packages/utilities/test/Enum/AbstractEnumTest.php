<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Enum;

use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Enum\EnumTranslatableInterface;
use Windwalker\Utilities\Test\Stub\Enum\StubBackedEnum;

/**
 * The NativeEnumTest class.
 */
abstract class AbstractEnumTest extends TestCase
{
    /**
     * getEnumClass
     *
     * @return  EnumTranslatableInterface
     */
    abstract protected static function getEnumClass(): string;

    public function testFrom(): void
    {
        $class = static::getEnumClass();

        self::assertTrue(
            $class::BAR()->sameAs($class::from('bar')),
        );
    }

    public function testTryFrom()
    {
        $class = static::getEnumClass();

        self::assertNull(
            $class::tryFrom('hello')
        );
    }

    public function testValues()
    {
        $class = static::getEnumClass();
        $values = $class::values();

        self::assertTrue($class::BAR()->sameAs($values['BAR']));
        self::assertTrue($class::YOO()->sameAs($values['YOO']));
    }

    public function testSameAs()
    {
        $class = static::getEnumClass();

        self::assertTrue($class::BAR()->sameAs('bar'));
        self::assertTrue($class::BAR()->sameAs($class::BAR()));
    }

    public function testMeta()
    {
        $class = static::getEnumClass();

        self::assertEquals('八八八', $class::BAR()->getTitle());
        self::assertEquals('fa fa-bars', $class::BAR()->getIcon());
        self::assertEquals('primary', $class::BAR()->getColor());
        self::assertTrue($class::BAR()->getMeta()['start']);
    }
}
