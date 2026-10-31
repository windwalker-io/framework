<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Str;

class StrTest extends TestCase
{
    #[Test]
    #[DataProvider('getCharProvider')]
    public function getCharReturnsMultibyteCharacterAtPosition(int $pos, string $expected)
    {
        self::assertEquals($expected, Str::getChar('白日依山盡', $pos));
    }

    public static function getCharProvider(): array
    {
        return [
            [0, '白'],
            [3, '山'],
            [10, ''],
            [-1, '盡'],
            [-2, '山'],
            [-5, '白'],
            [-16, ''],
        ];
    }

    #[Test]
    #[DataProvider('betweenProvider')]
    public function betweenReturnsTextEnclosedByDelimiters($string, $expected, $left, $right, $offset = 0)
    {
        self::assertEquals($expected, Str::between($string, $left, $right, $offset));
    }

    public static function betweenProvider(): array
    {
        return [
            ['fòôbàř', 'ôb', 'ò', 'à'],
            ['To {be} or {not} to be', 'be', '{', '}'],
            ['To {be} or {not} to be', 'not', '{', '}', 4],
            ['To {{be} or {not} to be', '{be', '{', '}'],
            ['{foo} and {bar}', 'bar', '{', '}', 1],
            ['foo and {bar', '', '{', '}', 1],
            ['foo} and bar', '', '{', '}', 1],
        ];
    }

    #[Test]
    public function collapseWhitespacesMergesRepeatedSpaces(): void
    {
        self::assertEquals('foo bar yoo', Str::collapseWhitespaces('foo  bar    yoo'));
    }

    #[Test]
    public function collapseWhitespacesTrimsSurroundingSpaces(): void
    {
        self::assertEquals('foo bar yoo', Str::collapseWhitespaces('  foo  bar yoo '));
    }

    #[Test]
    public function collapseWhitespacesMergesNewlinesAndCarriageReturns(): void
    {
        self::assertEquals('foo bar yoo', Str::collapseWhitespaces("  foo\n \r bar\n\r\n yoo \n"));
    }

    #[Test]
    #[DataProvider('containsProvider')]
    public function containsDetectsSubstringWithCaseSensitivityOption($expected, $string, $search, $caseSensitive = true)
    {
        self::assertSame($expected, Str::contains($string, $search, $caseSensitive));
    }

    public static function containsProvider(): array
    {
        return [
            [true, 'foobar', 'oba'],
            [true, 'fooBar', 'oba', false],
            [false, 'fooBar', 'oba'],
            [true, 'fòôbàř', 'ôbà'],
            [true, '白日依山盡', '日依'],
            [false, '白日依山盡', '梅友仁'],
            [false, 'FÒÔbàř', 'ôbà'],
            [true, 'FÒÔbàř', 'ôbà', false],
        ];
    }

    #[Test]
    #[DataProvider('endsWithProvider')]
    public function endsWithDetectsSuffixWithCaseSensitivityOption($string, $search, $caseSensitive, $expected)
    {
        self::assertSame($expected, Str::endsWith($string, $search, $caseSensitive));
    }

    public static function endsWithProvider(): array
    {
        return [
            ['Foo', 'oo', Str::CASE_SENSITIVE, true],
            ['Foo', 'Oo', Str::CASE_SENSITIVE, false],
            ['Foo', 'Oo', Str::CASE_INSENSITIVE, true],
            ['Foo', 'ooooo', Str::CASE_SENSITIVE, false],
            ['Foo', 'uv', Str::CASE_SENSITIVE, false],
            ['黃河入海流', '入海流', Str::CASE_SENSITIVE, true],
            ['黃河入海流', '入海流', Str::CASE_INSENSITIVE, true],
            ['黃河入海流', '依山盡', Str::CASE_SENSITIVE, false],
            ['FÒÔbà', 'ôbà', Str::CASE_SENSITIVE, false],
            ['FÒÔbà', 'ôbà', Str::CASE_INSENSITIVE, true],
        ];
    }

    #[Test]
    #[DataProvider('startsWithProvider')]
    public function startsWithDetectsPrefixWithCaseSensitivityOption(string $string, string $search, bool $caseSensitive, bool $expected)
    {
        self::assertSame($expected, Str::startsWith($string, $search, $caseSensitive));
    }

    public static function startsWithProvider(): array
    {
        return [
            ['Foo', 'Fo', Str::CASE_SENSITIVE, true],
            ['Foo', 'fo', Str::CASE_SENSITIVE, false],
            ['Foo', 'fo', Str::CASE_INSENSITIVE, true],
            ['Foo', 'foooo', Str::CASE_SENSITIVE, false],
            ['Foo', 'uv', Str::CASE_SENSITIVE, false],
            ['黃河入海流', '黃河', Str::CASE_SENSITIVE, true],
            ['黃河入海流', '黃河', Str::CASE_INSENSITIVE, true],
            ['黃河入海流', '依山盡', Str::CASE_SENSITIVE, false],
            ['FÒÔbà', 'fò', Str::CASE_SENSITIVE, false],
            ['FÒÔbà', 'fò', Str::CASE_INSENSITIVE, true],
        ];
    }

    #[Test]
    #[DataProvider('ensureLeftProvider')]
    public function ensureLeftPrependsPrefixOnlyWhenMissing(string $string, string $search, string $expected)
    {
        self::assertSame($expected, Str::ensureLeft($string, $search));
    }

    public static function ensureLeftProvider(): array
    {
        return [
            ['FlowerSakura', 'Flower', 'FlowerSakura'],
            ['Sakura', 'Flower', 'FlowerSakura'],
            ['FlowerSakura', 'flower', 'flowerFlowerSakura'],
            ['黃河入海流', '黃河', '黃河入海流'],
            ['入海流', '黃河', '黃河入海流'],
            ['FÒÔbà', 'FÒÔ', 'FÒÔbà'],
            ['FÒÔbà', 'fòô', 'fòôFÒÔbà'],
        ];
    }

    #[Test]
    #[DataProvider('ensureRightProvider')]
    public function ensureRightAppendsSuffixOnlyWhenMissing(string $string, string $search, string $expected)
    {
        self::assertSame($expected, Str::ensureRight($string, $search));
    }

    public static function ensureRightProvider(): array
    {
        return [
            ['FlowerSakura', 'Sakura', 'FlowerSakura'],
            ['Flower', 'Sakura', 'FlowerSakura'],
            ['FlowerSakura', 'sakura', 'FlowerSakurasakura'],
            ['黃河入海流', '海流', '黃河入海流'],
            ['黃河入', '海流', '黃河入海流'],
            ['FÒÔbà', 'Ôbà', 'FÒÔbà'],
            ['FÒÔbà', 'ôbà', 'FÒÔbàôbà'],
        ];
    }

    #[Test]
    #[DataProvider('hasLowerCaseProvider')]
    public function hasLowerCaseDetectsLowerCaseCharacters(string $string, bool $expected)
    {
        self::assertSame($expected, Str::hasLowerCase($string));
    }

    public static function hasLowerCaseProvider(): array
    {
        return [
            ['Foo', true],
            ['FOO', false],
            ['FÒô', true],
            ['FÒÔ', false],
            ['白日依山盡', false],
        ];
    }

    #[Test]
    #[DataProvider('hasUpperCaseProvider')]
    public function hasUpperCaseDetectsUpperCaseCharacters(string $string, bool $expected)
    {
        self::assertSame($expected, Str::hasUpperCase($string));
    }

    public static function hasUpperCaseProvider(): array
    {
        return [
            ['Foo', true],
            ['foo', false],
            ['FÒô', true],
            ['fòô', false],
            ['白日依山盡', false],
        ];
    }

    #[Test]
    #[DataProvider('insertProvider')]
    public function insertAddsSubstringAtPosition(string $string, string $insert, int $position, string $expected)
    {
        self::assertEquals($expected, Str::insert($string, $insert, $position));
    }

    public static function insertProvider(): array
    {
        return [
            ['FlowerSakura', 'And', 6, 'FlowerAndSakura'],
            ['fòàř', 'ôb', 2, 'fòôbàř'],
            ['白日山盡', '依', 2, '白日依山盡'],
            ['白日山盡', '依', 6, '白日山盡'],
        ];
    }

    #[Test]
    #[DataProvider('isLowerCaseProvider')]
    public function isLowerCaseDetectsAllLowerCaseString(string $string, bool $expected)
    {
        self::assertSame($expected, Str::isLowerCase($string));
    }

    public static function isLowerCaseProvider(): array
    {
        return [
            ['flower', true],
            ['Flower', false],
            ['fòôbàř', true],
            ['fòÔbàř', false],
            ['白日依山盡', false],
        ];
    }

    #[Test]
    #[DataProvider('isUpperCaseProvider')]
    public function isUpperCaseDetectsAllUpperCaseString(string $string, bool $expected)
    {
        self::assertSame($expected, Str::isUpperCase($string));
    }

    public static function isUpperCaseProvider(): array
    {
        return [
            ['FLOWER', true],
            ['Flower', false],
            ['FÒÔBÀŘ', true],
            ['fòÔbàř', false],
            ['白日依山盡', false],
        ];
    }

    #[Test]
    #[DataProvider('firstProvider')]
    public function firstReturnsLeadingCharacters(string $string, int $length, string $expected)
    {
        self::assertEquals($expected, Str::first($string, $length));
    }

    public static function firstProvider(): array
    {
        return [
            ['Foobar', 1, 'F'],
            ['Foobar', 3, 'Foo'],
            ['fòôbàř', 1, 'f'],
            ['fòôbàř', 3, 'fòô'],
            ['白日依山盡', 1, '白'],
            ['白日依山盡', 3, '白日依'],
            ['白日依山盡', 8, '白日依山盡'],
            ['白日依山盡', 0, ''],
            ['白日依山盡', -3, ''],
            ['', 5, ''],
        ];
    }

    #[Test]
    #[DataProvider('lastProvider')]
    public function lastReturnsTrailingCharacters(string $string, int $length, string $expected)
    {
        self::assertEquals($expected, Str::last($string, $length));
    }

    public static function lastProvider(): array
    {
        return [
            ['Foobar', 1, 'r'],
            ['Foobar', 3, 'bar'],
            ['fòôbàř', 1, 'ř'],
            ['fòôbàř', 3, 'bàř'],
            ['白日依山盡', 1, '盡'],
            ['白日依山盡', 3, '依山盡'],
            ['白日依山盡', 8, '白日依山盡'],
            ['白日依山盡', 0, ''],
            ['白日依山盡', -8, ''],
            ['', 5, ''],
        ];
    }

    #[Test]
    #[DataProvider('intersectStartProvider')]
    public function intersectStartReturnsCommonPrefix(string $string1, string $string2, string $expected)
    {
        self::assertEquals($expected, Str::intersectStart($string1, $string2));
    }

    #[Test]
    #[DataProvider('intersectStartProvider')]
    public function intersectLeftReturnsCommonPrefix(string $string1, string $string2, string $expected)
    {
        self::assertEquals($expected, Str::intersectLeft($string1, $string2));
    }

    public static function intersectStartProvider(): array
    {
        return [
            ['foobar', 'foo bar', 'foo'],
            ['foobar', 'frank', 'f'],
            ['foobar', 'sakura', ''],
            ['foobar', '', ''],
            ['fòôbàř', 'fòô bàř', 'fòô'],
            ['fòôbàř', 'fòôbàř', 'fòôbàř'],
            ['fòôbàř', 'fbàř òô', 'f'],
            ['白日依山盡', '白日夢', '白日'],
            ['白日依山盡', '', ''],
        ];
    }

    #[Test]
    #[DataProvider('intersectEndProvider')]
    public function intersectEndReturnsCommonSuffix(string $string1, string $string2, string $expected)
    {
        self::assertEquals($expected, Str::intersectEnd($string1, $string2));
    }

    #[Test]
    #[DataProvider('intersectEndProvider')]
    public function intersectRightReturnsCommonSuffix(string $string1, string $string2, string $expected)
    {
        self::assertEquals($expected, Str::intersectRight($string1, $string2));
    }

    public static function intersectEndProvider(): array
    {
        return [
            ['foobar', 'foo bar', 'bar'],
            ['foobar', 'lunar', 'ar'],
            ['foobar', 'sakura', ''],
            ['foobar', '', ''],
            ['fòôbàř', 'fòô bàř', 'bàř'],
            ['fòôbàř', 'fòôbàř', 'fòôbàř'],
            ['fòôbàř', 'fbàòôř', 'ř'],
            ['白日依山盡', '無盡', '盡'],
            ['白日依山盡', '', ''],
        ];
    }

    #[Test]
    #[DataProvider('intersectProvider')]
    public function intersectReturnsLongestCommonSubstring(string $string1, string $string2, string $expected)
    {
        self::assertEquals($expected, Str::intersect($string1, $string2));
    }

    public static function intersectProvider(): array
    {
        return [
            ['foobar', 'f oob ar', 'oob'],
            ['foobar', 'neobike', 'ob'],
            ['foobar', 'uncle', ''],
            ['foobar', '', ''],
            ['fòôbàř', 'f òôb àř', 'òôb'],
            ['fòôbàř', 'fòôbàř', 'fòôbàř'],
            ['fòôbàř', 'fbàòôř', 'òô'],
            ['漢字順序不一定影響閱讀', '漢字序順不一定影閱響讀', '不一定影'],
            ['白日依山盡', '', ''],
        ];
    }

    #[Test]
    #[DataProvider('padProvider')]
    public function padPadsStringOnBothSides(string $string, string $substring, int $length, string $expected)
    {
        self::assertEquals($expected, Str::pad($string, $length, $substring));
    }

    public static function padProvider(): array
    {
        return [
            ['foobar', '_', -1, 'foobar'],
            ['foobar', '_', 3, 'foobar'],
            ['foobar', '_/', 9, '_foobar_/'],
            ['foobar', '_/', 10, '_/foobar_/'],
            ['fòôbàř', '¬ø', 9, '¬fòôbàř¬ø'],
            ['fòôbàř', '¬øÿ', 11, '¬øfòôbàř¬øÿ'],
            ['妳好', '嗨', 5, '嗨妳好嗨嗨'],
        ];
    }

    #[Test]
    #[DataProvider('padStartProvider')]
    public function padStartPadsStringOnLeft(string $string, string $substring, int $length, string $expected)
    {
        self::assertEquals($expected, Str::padStart($string, $length, $substring));
    }

    #[Test]
    #[DataProvider('padStartProvider')]
    public function padLeftPadsStringOnLeft(string $string, string $substring, int $length, string $expected)
    {
        self::assertEquals($expected, Str::padLeft($string, $length, $substring));
    }

    public static function padStartProvider(): array
    {
        return [
            ['foobar', '_', -1, 'foobar'],
            ['foobar', '_', 3, 'foobar'],
            ['foobar', '_/', 9, '_/_foobar'],
            ['foobar', '_/', 10, '_/_/foobar'],
            ['fòôbàř', '¬ø', 9, '¬ø¬fòôbàř'],
            ['fòôbàř', '¬øÿ', 11, '¬øÿ¬øfòôbàř'],
            ['妳好', '嗨', 5, '嗨嗨嗨妳好'],
        ];
    }

    #[Test]
    #[DataProvider('padEndProvider')]
    public function padEndPadsStringOnRight(string $string, string $substring, int $length, string $expected)
    {
        self::assertEquals($expected, Str::padEnd($string, $length, $substring));
    }

    #[Test]
    #[DataProvider('padEndProvider')]
    public function padRightPadsStringOnRight(string $string, string $substring, int $length, string $expected)
    {
        self::assertEquals($expected, Str::padRight($string, $length, $substring));
    }

    public static function padEndProvider(): array
    {
        return [
            ['foobar', '_', -1, 'foobar'],
            ['foobar', '_', 3, 'foobar'],
            ['foobar', '_/', 9, 'foobar_/_'],
            ['foobar', '_/', 10, 'foobar_/_/'],
            ['fòôbàř', '¬ø', 9, 'fòôbàř¬ø¬'],
            ['fòôbàř', '¬øÿ', 11, 'fòôbàř¬øÿ¬ø'],
            ['妳好', '嗨', 5, '妳好嗨嗨嗨'],
        ];
    }

    #[Test]
    #[DataProvider('removeProvider')]
    public function removeDeletesCharacterAtOffset(string $string, int $offset, string $expected)
    {
        self::assertEquals($expected, Str::removeChar($string, $offset));
    }

    public static function removeProvider(): array
    {
        return [
            ['Foobar', 3, 'Fooar'],
            ['桃之夭夭', 0, '之夭夭'],
            ['下筆千言', 2, '下筆言'],
            ['白日依山盡', -2, '白日依盡'],
            ['白日依山盡', -5, '日依山盡'],
            ['白日依山盡', 5, '白日依山盡'],
            ['下筆千言', 6, '下筆千言'],
            ['下筆千言', -8, '下筆千言'],
        ];
    }

    #[Test]
    #[DataProvider('removeLeftProvider')]
    public function removeLeftStripsLeadingSubstring($string, $search, $expected)
    {
        self::assertEquals($expected, Str::removeLeft($string, $search));
    }

    public static function removeLeftProvider(): array
    {
        return [
            ['foobar', 'hoo', 'foobar'],
            ['foobar', 'fo', 'obar'],
            ['fòôbàř', 'òôb', 'fòôbàř'],
            ['fòôbàř', 'fò', 'ôbàř'],
            ['白日依山盡', '入海流', '白日依山盡'],
            ['白日依山盡', '白日', '依山盡'],
            ['白日依山盡', '', '白日依山盡'],
            ['', '白日', ''],
        ];
    }

    #[Test]
    #[DataProvider('removeRightProvider')]
    public function removeRightStripsTrailingSubstring($string, $search, $expected)
    {
        self::assertEquals($expected, Str::removeRight($string, $search));
    }

    public static function removeRightProvider(): array
    {
        return [
            ['foobar', 'hoo', 'foobar'],
            ['foobar', 'ar', 'foob'],
            ['fòôbàř', 'òôb', 'fòôbàř'],
            ['fòôbàř', 'àř', 'fòôb'],
            ['白日依山盡', '入海流', '白日依山盡'],
            ['白日依山盡', '依山盡', '白日'],
            ['白日依山盡', '', '白日依山盡'],
            ['', '白日', ''],
        ];
    }

    #[Test]
    #[DataProvider('sliceProvider')]
    public function sliceReturnsMultibyteRange(string $string, int $start, ?int $end = null, string $expected = '')
    {
        self::assertEquals($expected, Str::slice($string, $start, $end));
    }

    public static function sliceProvider(): array
    {
        return [
            ['Foobar Allstar', 0, 3, 'Foo'],
            ['Foobar Allstar', 3, 10, 'bar All'],
            ['Foobar Allstar', 3, null, 'bar Allstar'],
            ['Foobar Allstar', 3, -10, 'b'],
            ['Foobar Allstar', 10, 3, ''],
            ['fòôbàř àřfòôbf', 0, 3, 'fòô'],
            ['fòôbàř àřfòôbf', 3, 10, 'bàř àřf'],
            ['fòôbàř àřfòôbf', 3, null, 'bàř àřfòôbf'],
            ['fòôbàř àřfòôbf', 3, -10, 'b'],
            ['fòôbàř àřfòôbf', 10, 3, ''],
            ['白日依山盡 黃河入海流', 0, 3, '白日依'],
            ['白日依山盡 黃河入海流', 3, 10, '山盡 黃河入海'],
            ['白日依山盡 黃河入海流', 3, 15, '山盡 黃河入海流'],
        ];
    }

    #[Test]
    #[DataProvider('substringProvider')]
    public function substringReturnsMultibyteSubstring(string $string, int $start, ?int $end = null, string $expected = '')
    {
        self::assertEquals($expected, Str::substring($string, $start, $end));
    }

    public static function substringProvider(): array
    {
        return [
            ['Foobar Allstar', 0, 3, 'Foo'],
            ['Foobar Allstar', 3, 10, 'bar All'],
            ['Foobar Allstar', 3, null, 'bar Allstar'],
            ['Foobar Allstar', 3, -10, 'b'],
            ['Foobar Allstar', 10, 3, 'bar All'],
            ['fòôbàř àřfòôbf', 0, 3, 'fòô'],
            ['fòôbàř àřfòôbf', 3, 10, 'bàř àřf'],
            ['fòôbàř àřfòôbf', 3, null, 'bàř àřfòôbf'],
            ['fòôbàř àřfòôbf', 3, -10, 'b'],
            ['fòôbàř àřfòôbf', 10, 3, 'bàř àřf'],
            ['白日依山盡 黃河入海流', 0, 3, '白日依'],
            ['白日依山盡 黃河入海流', 3, 10, '山盡 黃河入海'],
            ['白日依山盡 黃河入海流', 3, 15, '山盡 黃河入海流'],
        ];
    }

    #[Test]
    #[DataProvider('wrapProvider')]
    public function surroundsWrapsStringWithSubstring(string $string, string $expected, $substring = null)
    {
        if ($substring === null) {
            self::assertEquals($expected, Str::surrounds($string));
        } else {
            self::assertEquals($expected, Str::surrounds($string, $substring));
        }
    }

    public static function wrapProvider(): array
    {
        return [
            ['foo', '"foo"'],
            ['foo', '"foo"', '"'],
            ['foo', '"foo"', ['"', '"']],
            ['foo', '[foo]', ['[', ']']],
        ];
    }

    #[Test]
    #[DataProvider('toggleCaseProvider')]
    public function toggleCaseInvertsCharacterCase(string $string, string $expected)
    {
        self::assertEquals($expected, Str::toggleCase($string));
    }

    public static function toggleCaseProvider(): array
    {
        return [
            ['FooBar', 'fOObAR'],
            ['foobar', 'FOOBAR'],
            ['FòôBàř', 'fÒÔbÀŘ'],
            ['fòôbàř', 'FÒÔBÀŘ'],
            ['白日依山盡', '白日依山盡'],
        ];
    }

    #[Test]
    #[DataProvider('truncateProvider')]
    public function truncateShortensStringWithSuffixAndWordBreak(
        string $string,
        int $length,
        string $expected = '',
        string $suffix = '',
        bool $wordBreak = true
    ) {
        self::assertEquals($expected, Str::truncate($string, $length, $suffix, $wordBreak));
    }

    public static function truncateProvider(): array
    {
        return [
            ['Hello foo bar', 5, 'Hello'],
            ['Hello foo bar', 5, 'Hello...', '...'],
            ['Hello foo bar', 6, 'Hello ...', '...'],
            ['Hello foo bar', 8, 'Hello fo...', '...'],
            ['Hello foo bar', 8, 'Hello...', '...', false],
            ['Hello foo bar', 13, 'Hello foo bar'],
            ['Hello foo bar', 15, 'Hello foo bar'],
            ['Hello foo bar', 15, 'Hello foo bar'],
            ['白日依山盡 黃河入海流', 4, '白日依山'],
            ['白日依山盡 黃河入海流', 4, '白日依山...', '...'],
        ];
    }

    #[Test]
    public function mapAppliesCallbackToEachCharacter(): void
    {
        $actual = Str::map(
            'Foo/Bar/Yoo',
            static function ($char, $key) {
                return $char === '/' ? '_' : $char;
            }
        );

        self::assertEquals('Foo_Bar_Yoo', (string) $actual);
    }

    #[Test]
    public function mapAcceptsCallableName(): void
    {
        $actual = Str::map('Foo/Bar/Yoo', 'strtoupper');

        self::assertEquals('FOO/BAR/YOO', (string) $actual);
    }

    #[Test]
    public function filterKeepsCharactersAcceptedByCallback(): void
    {
        $actual = Str::filter(
            'Foo/Bar/Yoo',
            static function ($char, $key) {
                return $char !== '/';
            }
        );

        self::assertEquals('FooBarYoo', (string) $actual);
    }

    #[Test]
    public function filterAcceptsCallableName(): void
    {
        $actual = Str::filter('Foo1Bar2Yoo', 'is_numeric');

        self::assertEquals('12', (string) $actual);
    }

    #[Test]
    public function rejectRemovesCharactersMatchedByCallback(): void
    {
        $actual = Str::reject(
            'Foo/Bar/Yoo',
            static function ($char, $key) {
                return $char === '/';
            }
        );

        self::assertEquals('FooBarYoo', (string) $actual);
    }

    #[Test]
    public function rejectAcceptsCallableName(): void
    {
        $actual = Str::reject('Foo1Bar2Yoo', 'is_numeric');

        self::assertEquals('FooBarYoo', (string) $actual);
    }

    #[Test]
    public function incrementAppendsAndBumpsDefaultCounterSuffix(): void
    {
        $title = 'Foo Bar';

        self::assertEquals('Foo Bar (2)', $title = Str::increment($title));
        self::assertEquals('Foo Bar (3)', $title = Str::increment($title));
    }

    #[Test]
    public function incrementAppendsAndBumpsCounterWithCustomFormat(): void
    {
        $title = 'Foo Bar';

        self::assertEquals('Foo Bar-2', $title = Str::increment($title, '%s-%d'));
        self::assertEquals('Foo Bar-3', $title = Str::increment($title, '%s-%d'));
    }

    #[Test]
    public function numToAlphaConvertsNumbersToSpreadsheetStyleLetters(): void
    {
        self::assertEquals('A', Str::numToAlpha(0));
        self::assertEquals('F', Str::numToAlpha(5));
        self::assertEquals('Z', Str::numToAlpha(25));
        self::assertEquals('AA', Str::numToAlpha(26));
        self::assertEquals('AB', Str::numToAlpha(27));
        self::assertEquals('HJUNYVA', Str::numToAlpha(26 * 100000000));
        self::assertEquals('OHNRDMZI', Str::numToAlpha(123123123456));
    }

    #[Test]
    public function alphaToNumConvertsSpreadsheetStyleLettersToNumbers(): void
    {
        self::assertEquals(0, Str::alphaToNum('A'));
        self::assertEquals(5, Str::alphaToNum('F'));
        self::assertEquals(25, Str::alphaToNum('Z'));
        self::assertEquals(26, Str::alphaToNum('AA'));
        self::assertEquals(27, Str::alphaToNum('AB'));
        self::assertEquals(26 * 100000000, Str::alphaToNum('HJUNYVA'));
        self::assertEquals(123123123456, Str::alphaToNum('OHNRDMZI'));
    }

    // -- safeReplace tests ------------------------------------------------

    #[Test]
    #[DataProvider('safeReplaceProvider')]
    public function safeReplaceReplacesWithoutChainingAndCountsReplacements(
        string|array $search,
        string|array $replace,
        string|array $subject,
        string|array $expected,
        ?int $expectedCount = null,
    ): void {
        $count = 0;
        $result = Str::safeReplace($search, $replace, $subject, $count);

        self::assertSame($expected, $result);

        if ($expectedCount !== null) {
            self::assertSame($expectedCount, $count);
        }
    }

    public static function safeReplaceProvider(): array
    {
        return [
            'basic replacement' => [
                '{name}',
                'World',
                'Hello {name}',
                'Hello World',
            ],
            'preserves escaped token' => [
                '{name}',
                'World',
                'Hello {name} and \{name}',
                'Hello World and \{name}',
            ],
            'multiple occurrences with escaped' => [
                '{x}',
                '1',
                '{x} + {x} = \{x}',
                '1 + 1 = \{x}',
                2,
            ],
            'array search and replace' => [
                ['{first}', '{last}'],
                ['John', 'Doe'],
                'Name: {first} {last}, escaped: \{first} \{last}',
                'Name: John Doe, escaped: \{first} \{last}',
            ],
            'count only unescaped' => [
                '{v}',
                'x',
                '{v} {v} \{v} {v}',
                'x x \{v} x',
                3,
            ],
            'no match' => [
                '{miss}',
                'hit',
                'nothing to replace',
                'nothing to replace',
                0,
            ],
            'subject array' => [
                '{k}',
                'v',
                ['{k} here', '\{k} safe', 'no match'],
                ['v here', '\{k} safe', 'no match'],
            ],
            'empty search' => [
                '',
                'x',
                'hello',
                'hello',
                0,
            ],
            'only escaped tokens' => [
                '{token}',
                'replaced',
                '\{token}',
                '\{token}',
                0,
            ],
            'multiple different tokens' => [
                ['{{', '}}'],
                ['[', ']'],
                '{{ content }} and \{{ literal \}}',
                '[ content ] and \{{ literal \}}',
            ],
        ];
    }

    #[Test]
    public function removeStartAndEndSupportAsciiEncoding(): void
    {
        self::assertSame('bar', Str::removeStart('foobar', 'foo', 'ascii'));
        self::assertSame('foobar', Str::removeStart('foobar', 'bar', 'ascii'));
        self::assertSame('foo', Str::removeEnd('foobar', 'bar', 'ascii'));
        self::assertSame('foobar', Str::removeEnd('foobar', 'foo', 'ascii'));
    }
}
