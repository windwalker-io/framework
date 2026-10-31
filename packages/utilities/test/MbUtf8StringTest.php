<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use BadMethodCallException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Utf8String;

class MbUtf8StringTest extends TestCase
{
    use MbstringTestTrait;

    #[Test]
    public function callStaticThrowsBadMethodCallExceptionForUnknownMethod()
    {
        $this->expectException(BadMethodCallException::class);

        Utf8String::noexists('test');
    }

    #[Test]
    #[DataProvider('isAsciiProvider')]
    public function isAsciiDetectsAsciiOnlyStrings($string, $expected)
    {
        $this->assertEquals(
            $expected,
            Utf8String::isAscii($string),
        );
    }

    #[Test]
    #[DataProvider('strposProvider')]
    public function strposFindsMultibyteNeedlePosition($expect, $haystack, $needle, $offset = 0)
    {
        $actual = Utf8String::strpos($haystack, $needle, $offset);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('strrposProvider')]
    public function strrposFindsLastMultibyteNeedlePosition($expect, $haystack, $needle, $offset = 0)
    {
        $actual = Utf8String::strrpos($haystack, $needle, $offset);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('substrProvider')]
    public function substrExtractsMultibyteSubstring($expect, $string, $start, $length = null)
    {
        $actual = Utf8String::substr($string, $start, $length);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('strtolowerProvider')]
    public function strtolowerConvertsMultibyteStringToLowerCase($string, $expect): void
    {
        $actual = Utf8String::strtolower($string);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('strtoupperProvider')]
    public function strtoupperConvertsMultibyteStringToUpperCase($string, $expect): void
    {
        $actual = Utf8String::strtoupper($string);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('strlenProvider')]
    public function strlenCountsMultibyteCharacters($string, $expect): void
    {
        $actual = Utf8String::strlen($string);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('strIreplaceProvider')]
    public function strIreplaceReplacesCaseInsensitivelyAndReportsCount($search, $replace, $subject, $count, $expect): void
    {
        $actual = Utf8String::strIreplace($search, $replace, $subject, $count);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('strSplitProvider')]
    public function strSplitSplitsMultibyteStringByLength($string, $split_length, $expect): void
    {
        $actual = Utf8String::strSplit($string, $split_length);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('strcasecmpProvider')]
    public function strcasecmpComparesStringsCaseInsensitively($string1, $string2, $expect)
    {
        $actual = Utf8String::strcasecmp($string1, $string2);

        if ($actual !== 0) {
            $actual /= abs($actual);
        }

        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('strcmpProvider')]
    public function strcmpComparesStringsCaseSensitively($string1, $string2, $expect): void
    {
        $actual = Utf8String::strcmp($string1, $string2);

        if ($actual !== 0) {
            $actual /= abs($actual);
        }

        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('strcspnProvider')]
    public function strcspnCountsLeadingCharactersNotInMask($haystack, $needles, $start, $len, $expect): void
    {
        $actual = Utf8String::strcspn($haystack, $needles, $start, $len);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('stristrProvider')]
    public function stristrReturnsRemainderFromCaseInsensitiveMatch($haystack, $needle, $expect): void
    {
        $actual = Utf8String::stristr($haystack, $needle);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('strrevProvider')]
    public function strrevReversesMultibyteString($string, $expect): void
    {
        $actual = Utf8String::strrev($string);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('strspnProvider')]
    public function strspnCountsLeadingCharactersInMask($subject, $mask, $start, $length, $expect): void
    {
        $actual = Utf8String::strspn($subject, $mask, $start, $length);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('substrReplaceProvider')]
    public function substrReplaceReplacesMultibyteSegment($expect, $string, $replacement, $start, $length): void
    {
        $actual = Utf8String::substrReplace($string, $replacement, $start, $length);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('ltrimProvider')]
    public function ltrimStripsLeadingCharacters(string $string, ?string $charlist, ?string $expect): void
    {
        if ($charlist === null) {
            $actual = Utf8String::ltrim($string);
        } else {
            $actual = Utf8String::ltrim($string, $charlist);
        }

        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('rtrimProvider')]
    public function rtrimStripsTrailingCharacters(string $string, ?string $charlist, string $expect): void
    {
        if ($charlist === null) {
            $actual = Utf8String::rtrim($string);
        } else {
            $actual = Utf8String::rtrim($string, $charlist);
        }

        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('trimProvider')]
    public function trimStripsSurroundingCharacters($string, $charlist, $expect): void
    {
        if ($charlist === null) {
            $actual = Utf8String::trim($string);
        } else {
            $actual = Utf8String::trim($string, $charlist);
        }

        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('ucfirstProvider')]
    public function ucfirstUppercasesFirstCharacter($string, $expect): void
    {
        $actual = Utf8String::ucfirst($string);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('lcfirstProvider')]
    public function lcfirstLowercasesFirstCharacter($string, $expect)
    {
        $actual = Utf8String::lcfirst($string);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('ucwordsProvider')]
    public function ucwordsUppercasesFirstCharacterOfEachWord($string, $expect): void
    {
        $actual = Utf8String::ucwords($string);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('substrCountProvider')]
    public function substrCountCountsOccurrencesWithCaseSensitivityOption($string, $search, $expected, $caseSensitive)
    {
        self::assertEquals($expected, Utf8String::substrCount($string, $search, $caseSensitive));
    }

    #[Test]
    #[DataProvider('convertEncodingProvider')]
    public function convertEncodingConvertsBetweenEncodings($source, $from_encoding, $to_encoding, $expect): void
    {
        $actual = Utf8String::convertEncoding($source, $from_encoding, $to_encoding);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('isUtf8Provider')]
    public function isUtf8ValidatesUtf8Strings($string, $expect): void
    {
        $actual = Utf8String::isUtf8($string);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('unicodeToUtf8Provider')]
    public function unicodeToUtf8ConvertsCodePointsToUtf8($string, $expect): void
    {
        $actual = Utf8String::unicodeToUtf8($string);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('unicodeToUtf16Provider')]
    public function unicodeToUtf16ConvertsCodePointsToUtf16($string, $expect): void
    {
        $actual = Utf8String::unicodeToUtf16($string);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('isUtf8Provider')]
    public function compliantDetectsUtf8CompliantStrings($string, $expect): void
    {
        $actual = Utf8String::compliant($string);
        $this->assertEquals($expect, $actual);
    }

    #[Test]
    #[DataProvider('providerTestShuffle')]
    public function shuffleReordersCharactersPreservingCharacterCounts($string)
    {
        $result = Utf8String::shuffle($string);

        self::assertNotEquals($result, $string);
        self::assertEquals(strlen($result), strlen($string));

        $len = mb_strlen($string);

        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($string, $i, 1);
            $countBefore = mb_substr_count($string, $char);
            $countAfter = mb_substr_count($result, $char);

            self::assertEquals($countBefore, $countAfter);
        }
    }

    #[Test]
    public function toAsciiReturnsOneBasedCharacterCodes(): void
    {
        self::assertEquals(
            [
                1 => 70,
                2 => 108,
                3 => 111,
                4 => 119,
                5 => 101,
                6 => 114,
            ],
            Utf8String::toAscii('Flower'),
        );
    }
}
