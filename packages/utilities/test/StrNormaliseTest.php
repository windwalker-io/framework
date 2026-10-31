<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\StrNormalize;

class StrNormaliseTest extends TestCase
{
    public static function splitCamelCaseProvider(): array
    {
        return [
            // Note: string, expected
            ['FooBarABCDef', ['Foo', 'Bar', 'ABC', 'Def']],
            ['JFooBar', ['J', 'Foo', 'Bar']],
            ['J001FooBar002', ['J001', 'Foo', 'Bar002']],
            ['abcDef', ['abc', 'Def']],
            ['abc_defGhi_Jkl', ['abc_def', 'Ghi_Jkl']],
            ['ThisIsA_NASAAstronaut', ['This', 'Is', 'A_NASA', 'Astronaut']],
            ['JohnFitzgerald_Kennedy', ['John', 'Fitzgerald_Kennedy']],
        ];
    }

    public static function pascalCaseProvider(): array
    {
        return [
            ['FooBar', 'Foo Bar'],
            ['FooBar', 'Foo-Bar'],
            ['FooBar', 'Foo_Bar'],
            ['FooBar', 'foo bar'],
            ['FooBar', 'foo-bar'],
            ['FooBar', 'foo_bar'],
        ];
    }

    public static function kebabCaseProvider(): array
    {
        return [
            ['foo-bar', 'Foo Bar'],
            ['foo-bar', 'Foo-Bar'],
            ['foo-bar', 'Foo_Bar'],
            ['foo-bar', 'foo bar'],
            ['foo-bar', 'foo-bar'],
            ['foo-bar', 'foo_bar'],
            ['foo-bar', 'foo   bar'],
            ['foo-bar', 'foo---bar'],
            ['foo-bar', 'foo___bar'],
        ];
    }

    public static function spaceSeparatedProvider(): array
    {
        return [
            ['Foo Bar', 'Foo Bar'],
            ['Foo Bar', 'Foo-Bar'],
            ['Foo Bar', 'Foo_Bar'],
            ['foo bar', 'foo bar'],
            ['foo bar', 'foo-bar'],
            ['foo bar', 'foo_bar'],
            ['foo bar', 'foo   bar'],
            ['foo bar', 'foo---bar'],
            ['foo bar', 'foo___bar'],
        ];
    }

    public static function underscoreSeparatedProvider(): array
    {
        return [
            ['Foo_Bar', 'Foo Bar'],
            ['Foo_Bar', 'Foo-Bar'],
            ['Foo_Bar', 'Foo_Bar'],
            ['foo_bar', 'foo bar'],
            ['foo_bar', 'foo-bar'],
            ['foo_bar', 'foo_bar'],
            ['foo_bar', 'foo   bar'],
            ['foo_bar', 'foo---bar'],
            ['foo_bar', 'foo___bar'],
        ];
    }

    public static function variableProvider(): array
    {
        return [
            ['myFooBar', 'My Foo Bar'],
            ['myFooBar', 'My Foo-Bar'],
            ['myFooBar', 'My Foo_Bar'],
            ['myFooBar', 'my foo bar'],
            ['myFooBar', 'my foo-bar'],
            ['myFooBar', 'my foo_bar'],
        ];
    }

    public static function keyProvider(): array
    {
        return [
            ['foo_bar', 'Foo Bar'],
            ['foo_bar', 'Foo-Bar'],
            ['foo_bar', 'Foo_Bar'],
            ['foo_bar', 'foo bar'],
            ['foo_bar', 'foo-bar'],
            ['foo_bar', 'foo_bar'],
        ];
    }

    #[Test]
    #[DataProvider('splitCamelCaseProvider')]
    public function splitCamelCaseSplitsStringAtWordBoundaries(string $input, array $expected): void
    {
        $this->assertEquals($expected, StrNormalize::splitCamelCase($input));
    }

    #[Test]
    #[DataProvider('pascalCaseProvider')]
    public function toPascalCaseConvertsSeparatedWordsToPascalCase(string $expected, string $input): void
    {
        $this->assertEquals($expected, StrNormalize::toPascalCase($input));
    }

    #[Test]
    #[DataProvider('pascalCaseProvider')]
    public function toCamelCaseConvertsSeparatedWordsToLowerFirstCamelCase(string $expected, string $input): void
    {
        $this->assertEquals(lcfirst($expected), StrNormalize::toCamelCase($input));
    }

    #[Test]
    #[DataProvider('kebabCaseProvider')]
    public function toKebabCaseJoinsWordsWithDashes(string $expected, string $input): void
    {
        $this->assertEquals($expected, StrNormalize::toKebabCase($input));
    }

    #[Test]
    #[DataProvider('spaceSeparatedProvider')]
    public function toSpaceSeparatedJoinsWordsWithSingleSpace(string $expected, string $input): void
    {
        $this->assertEquals($expected, StrNormalize::toSpaceSeparated($input));
    }

    #[Test]
    #[DataProvider('underscoreSeparatedProvider')]
    public function toUnderscoreSeparatedJoinsWordsWithUnderscore(string $expected, string $input): void
    {
        $this->assertEquals($expected, StrNormalize::toUnderscoreSeparated($input));
    }

    #[Test]
    #[DataProvider('variableProvider')]
    public function toVariableConvertsWordsToLowerCamelCaseName(string $expected, string $input): void
    {
        $this->assertEquals($expected, StrNormalize::toVariable($input));
    }

    #[Test]
    #[DataProvider('keyProvider')]
    public function toKeyConvertsWordsToLowerSnakeCaseKey(string $expected, string $input): void
    {
        $this->assertEquals($expected, StrNormalize::toKey($input));
    }

    #[Test]
    public function splitByDropsEmptySegments(): void
    {
        self::assertSame(['foo', 'bar'], array_values(StrNormalize::splitBy('foo__bar_', '_')));
        self::assertSame(['a', 'b'], array_values(StrNormalize::splitBy('a-b', '-')));
    }

    #[Test]
    public function toDotSeparatedAndToSnakeCaseNormalizeSeparators(): void
    {
        self::assertSame('foo.bar.baz', StrNormalize::toDotSeparated('foo-bar_baz'));
        self::assertSame('foo_bar_baz', StrNormalize::toSnakeCase('Foo-Bar baz'));
    }

    #[Test]
    #[DataProvider('classNamespaceProvider')]
    public function toClassNamespaceNormalizesSeparatorsAndCasing(string $expected, string $input): void
    {
        self::assertSame($expected, StrNormalize::toClassNamespace($input));
    }

    public static function classNamespaceProvider(): array
    {
        return [
            ['Foo\\Bar\\Baz', 'foo/bar/baz'],
            ['Foo\\Bar', '\\foo\\bar\\'],
            ['FooBar\\Yoo', 'foo_bar//yoo'],
        ];
    }
}
