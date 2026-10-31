<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Enum;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Contract\LanguageInterface;
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

    #[Test]
    public function rawValuesMapsNamesToRawValues(): void
    {
        $class = static::getEnumClass();

        self::assertSame(['BAR' => 'bar', 'YOO' => 'yoo'], $class::rawValues());
    }

    #[Test]
    public function instanceCallWithConstantNameReturnsThatCase(): void
    {
        $class = static::getEnumClass();

        self::assertTrue($class::YOO()->sameAs($class::BAR()->YOO()));
    }

    #[Test]
    public function wrapAcceptsValuesAndInstances(): void
    {
        $class = static::getEnumClass();

        self::assertTrue($class::BAR()->sameAs($class::wrap('bar')));
        self::assertTrue($class::BAR()->sameAs($class::wrap($class::BAR())));
    }

    #[Test]
    public function tryWrapReturnsNullForNullAndUnknownValues(): void
    {
        $class = static::getEnumClass();

        self::assertNull($class::tryWrap(null));
        self::assertNull($class::tryWrap('hello'));
        self::assertTrue($class::YOO()->sameAs($class::tryWrap('yoo')));
        self::assertTrue($class::YOO()->sameAs($class::tryWrap($class::YOO())));
    }

    #[Test]
    public function casesReturnsAllInstancesInDeclarationOrder(): void
    {
        $class = static::getEnumClass();
        $cases = $class::cases();

        self::assertCount(2, $cases);
        self::assertTrue($class::BAR()->sameAs($cases[0]));
        self::assertTrue($class::YOO()->sameAs($cases[1]));
    }

    #[Test]
    public function keysAndToArrayDescribeConstants(): void
    {
        $class = static::getEnumClass();

        self::assertSame(['BAR', 'YOO'], $class::keys());
        self::assertSame(['BAR' => 'bar', 'YOO' => 'yoo'], $class::toArray());
    }

    #[Test]
    public function valueAndKeyAccessorsReturnRawData(): void
    {
        $class = static::getEnumClass();

        self::assertSame('bar', $class::BAR()->getValue());
        self::assertSame('YOO', $class::YOO()->getKey());
        self::assertSame('"bar"', json_encode($class::BAR()));
    }

    #[Test]
    public function sameAsIsFalseForOtherValues(): void
    {
        $class = static::getEnumClass();

        self::assertFalse($class::BAR()->sameAs('yoo'));
        self::assertFalse($class::BAR()->sameAs($class::YOO()));
    }

    #[Test]
    public function getTitlesIconsColorsAndMetasAreKeyedByValue(): void
    {
        $class = static::getEnumClass();

        self::assertSame(['bar' => '八八八', 'yoo' => '又又又'], $class::getTitles());
        self::assertSame(['bar' => 'fa fa-bars', 'yoo' => 'fa fa-file'], $class::getIcons());
        self::assertSame(['bar' => 'primary', 'yoo' => 'danger'], $class::getColors());
        self::assertSame(
            ['bar' => ['start' => true, 'end' => false], 'yoo' => ['start' => false, 'end' => true]],
            $class::getMetas()
        );
    }

    #[Test]
    public function fromTitleFindsCaseByTitle(): void
    {
        $class = static::getEnumClass();

        self::assertTrue($class::YOO()->sameAs($class::fromTitle('又又又')));
        self::assertTrue($class::BAR()->sameAs($class::tryFromTitle('八八八')));
        self::assertNull($class::tryFromTitle('not exists'));
    }

    #[Test]
    public function isHiddenIsFalseWithoutHiddenAttribute(): void
    {
        $class = static::getEnumClass();

        self::assertFalse($class::BAR()->isHidden());
    }

    #[Test]
    public function maxLengthIsLongestRawValueLength(): void
    {
        $class = static::getEnumClass();

        self::assertSame(3, $class::maxLength());
        self::assertSame(3, $class::maxLength());
    }

    #[Test]
    public function transDelegatesToLanguage(): void
    {
        $class = static::getEnumClass();
        $lang = $this->createStub(LanguageInterface::class);
        $lang->method('trans')->willReturnCallback(fn(string $id) => 'T:' . $id);

        self::assertSame('T:app.foo.BAR', $class::BAR()->trans($lang));
    }

    #[Test]
    public function getTitleUsesTranslatorWhenLanguageIsGiven(): void
    {
        $class = static::getEnumClass();
        $lang = $this->createStub(LanguageInterface::class);
        $lang->method('trans')->willReturnCallback(fn(string $id) => 'T:' . $id);

        self::assertSame('T:app.foo.YOO', $class::YOO()->getTitle($lang));
    }

    #[Test]
    public function getTransItemsMapsValuesToTranslatedTitles(): void
    {
        $class = static::getEnumClass();
        $lang = $this->createStub(LanguageInterface::class);
        $lang->method('trans')->willReturnCallback(fn(string $id) => 'T:' . $id);

        self::assertSame(
            ['bar' => 'T:app.foo.BAR', 'yoo' => 'T:app.foo.YOO'],
            $class::getTransItems($lang)
        );
    }
}
