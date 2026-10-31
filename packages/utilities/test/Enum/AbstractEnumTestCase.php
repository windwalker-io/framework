<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Enum;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Enum\EnumTranslatableInterface;

abstract class AbstractEnumTestCase extends TestCase
{
    /**
     * @return  class-string<EnumTranslatableInterface>
     */
    abstract protected static function getEnumClass(): string;

    #[Test]
    public function fromReturnsEnumInstanceForValue(): void
    {
        $class = static::getEnumClass();

        self::assertTrue(
            $class::BAR()->sameAs($class::from('bar')),
        );
    }

    #[Test]
    public function tryFromReturnsNullForUnknownValue(): void
    {
        $class = static::getEnumClass();

        self::assertNull(
            $class::tryFrom('hello')
        );
    }

    #[Test]
    public function valuesReturnsInstancesKeyedByName(): void
    {
        $class = static::getEnumClass();
        $values = $class::values();

        self::assertTrue($class::BAR()->sameAs($values['BAR']));
        self::assertTrue($class::YOO()->sameAs($values['YOO']));
    }

    #[Test]
    public function sameAsComparesByValueAndInstance(): void
    {
        $class = static::getEnumClass();

        self::assertTrue($class::BAR()->sameAs('bar'));
        self::assertTrue($class::BAR()->sameAs($class::BAR()));
    }

    #[Test]
    public function metaReturnsTitleIconColorAndExtraMeta(): void
    {
        $class = static::getEnumClass();

        self::assertEquals('八八八', $class::BAR()->getTitle());
        self::assertEquals('fa fa-bars', $class::BAR()->getIcon());
        self::assertEquals('primary', $class::BAR()->getColor());
        self::assertTrue($class::BAR()->getMeta()['start']);
    }
}
