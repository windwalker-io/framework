<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use BadMethodCallException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Utf8String;

/**
 * Test class of String
 *
 * @since 2.0
 */
class MbUtf8StringTest extends TestCase
{
    use MbstringTestTrait;

    /**
     * testCallStatic
     *
     * @return  void
     */
    public function testCallStatic()
    {
        $this->expectException(BadMethodCallException::class);

        Utf8String::noexists('test');
    }

    /**
     * Test...
     *
     * @param  string   $string    @todo
     * @param  boolean  $expected  @todo
     *
     * @return  void
     *
     */
    #[DataProvider('isAsciiProvider')]
    public function testIsAscii($string, $expected)
    {
        $this->assertEquals(
            $expected,
            Utf8String::isAscii($string),
        );
    }

    /**
     * Test...
     *
     * @param  string   $expect    @todo
     * @param  string   $haystack  @todo
     * @param  string   $needle    @todo
     * @param  integer  $offset    @todo
     *
     * @return  void
     *
     */
    #[DataProvider('strposProvider')]
    public function testStrpos($expect, $haystack, $needle, $offset = 0)
    {
        $actual = Utf8String::strpos($haystack, $needle, $offset);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string   $expect    @todo
     * @param  string   $haystack  @todo
     * @param  string   $needle    @todo
     * @param  integer  $offset    @todo
     *
     * @return  void
     *
     */
    #[DataProvider('strrposProvider')]
    public function testStrrpos($expect, $haystack, $needle, $offset = 0)
    {
        $actual = Utf8String::strrpos($haystack, $needle, $offset);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string    $expect  @todo
     * @param  string    $string  @todo
     * @param  string    $start   @todo
     * @param  bool|int  $length  @todo
     *
     * @return  void
     *
     */
    #[DataProvider('substrProvider')]
    public function testSubstr($expect, $string, $start, $length = null)
    {
        $actual = Utf8String::substr($string, $start, $length);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string  $string  @todo
     * @param  string  $expect  @todo
     *
     * @return  void
     *
     */
    #[DataProvider('strtolowerProvider')]
    public function testStrtolower($string, $expect): void
    {
        $actual = Utf8String::strtolower($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string  $string  @todo
     * @param  string  $expect  @todo
     *
     * @return  void
     *
     */
    #[DataProvider('strtoupperProvider')]
    public function testStrtoupper($string, $expect): void
    {
        $actual = Utf8String::strtoupper($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string  $string  @todo
     * @param  string  $expect  @todo
     *
     * @return  void
     *
     */
    #[DataProvider('strlenProvider')]
    public function testStrlen($string, $expect): void
    {
        $actual = Utf8String::strlen($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string   $search   @todo
     * @param  string   $replace  @todo
     * @param  string   $subject  @todo
     * @param  integer  $count    @todo
     * @param  string   $expect   @todo
     *
     * @return  void
     *
     */
    #[DataProvider('strIreplaceProvider')]
    public function testStrIreplace($search, $replace, $subject, $count, $expect): void
    {
        $actual = Utf8String::strIreplace($search, $replace, $subject, $count);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string  $string        @todo
     * @param  int     $split_length  @todo
     * @param  string  $expect        @todo
     *
     * @return  void
     *
     */
    #[DataProvider('strSplitProvider')]
    public function testStrSplit($string, $split_length, $expect): void
    {
        $actual = Utf8String::strSplit($string, $split_length);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string  $string1  @todo
     * @param  string  $string2  @todo
     * @param  string  $expect   @todo
     *
     * @return void
     *
     */
    #[DataProvider('strcasecmpProvider')]
    public function testStrcasecmp($string1, $string2, $expect)
    {
        $actual = Utf8String::strcasecmp($string1, $string2);

        if ($actual !== 0) {
            $actual /= abs($actual);
        }

        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string  $string1  @todo
     * @param  string  $string2  @todo
     * @param  string  $locale   @todo
     * @param  string  $expect   @todo
     *
     * @return  void
     *
     */
    #[DataProvider('strcmpProvider')]
    public function testStrcmp($string1, $string2, $expect): void
    {
        $actual = Utf8String::strcmp($string1, $string2);

        if ($actual !== 0) {
            $actual /= abs($actual);
        }

        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string   $haystack  @todo
     * @param  string   $needles   @todo
     * @param  integer  $start     @todo
     * @param  integer  $len       @todo
     * @param  string   $expect    @todo
     *
     * @return  void
     *
     */
    #[DataProvider('strcspnProvider')]
    public function testStrcspn($haystack, $needles, $start, $len, $expect): void
    {
        $actual = Utf8String::strcspn($haystack, $needles, $start, $len);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string  $haystack  @todo
     * @param  string  $needle    @todo
     * @param  string  $expect    @todo
     *
     * @return  void
     *
     */
    #[DataProvider('stristrProvider')]
    public function testStristr($haystack, $needle, $expect): void
    {
        $actual = Utf8String::stristr($haystack, $needle);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string  $string  @todo
     * @param  string  $expect  @todo
     *
     * @return  void
     *
     */
    #[DataProvider('strrevProvider')]
    public function testStrrev($string, $expect): void
    {
        $actual = Utf8String::strrev($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string   $subject  @todo
     * @param  string   $mask     @todo
     * @param  integer  $start    @todo
     * @param  integer  $length   @todo
     * @param  string   $expect   @todo
     *
     * @return  void
     *
     */
    #[DataProvider('strspnProvider')]
    public function testStrspn($subject, $mask, $start, $length, $expect): void
    {
        $actual = Utf8String::strspn($subject, $mask, $start, $length);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string   $expect       @todo
     * @param  string   $string       @todo
     * @param  string   $replacement  @todo
     * @param  integer  $start        @todo
     * @param  integer  $length       @todo
     *
     * @return  array
     *
     */
    #[DataProvider('substrReplaceProvider')]
    public function testSubstrReplace($expect, $string, $replacement, $start, $length): void
    {
        $actual = Utf8String::substrReplace($string, $replacement, $start, $length);
        $this->assertEquals($expect, $actual);
    }

    #[DataProvider('ltrimProvider')]
    public function testLtrim(string $string, ?string $charlist, ?string $expect): void
    {
        if ($charlist === null) {
            $actual = Utf8String::ltrim($string);
        } else {
            $actual = Utf8String::ltrim($string, $charlist);
        }

        $this->assertEquals($expect, $actual);
    }

    #[DataProvider('rtrimProvider')]
    public function testRtrim(string $string, ?string $charlist, string $expect): void
    {
        if ($charlist === null) {
            $actual = Utf8String::rtrim($string);
        } else {
            $actual = Utf8String::rtrim($string, $charlist);
        }

        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string  $string    @todo
     * @param  string  $charlist  @todo
     * @param  string  $expect    @todo
     *
     * @return  array
     *
     */
    #[DataProvider('trimProvider')]
    public function testTrim($string, $charlist, $expect): void
    {
        if ($charlist === null) {
            $actual = Utf8String::trim($string);
        } else {
            $actual = Utf8String::trim($string, $charlist);
        }

        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string  $string  @todo
     * @param  string  $expect  @todo
     *
     * @return  array
     *
     */
    #[DataProvider('ucfirstProvider')]
    public function testUcfirst($string, $expect): void
    {
        $actual = Utf8String::ucfirst($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * testLcfirst
     *
     * @param  string  $string
     * @param  string  $expect
     *
     * @return  void
     *
     */
    #[DataProvider('lcfirstProvider')]
    public function testLcfirst($string, $expect)
    {
        $actual = Utf8String::lcfirst($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string  $string  @todo
     * @param  string  $expect  @todo
     *
     * @return  array
     *
     */
    #[DataProvider('ucwordsProvider')]
    public function testUcwords($string, $expect): void
    {
        $actual = Utf8String::ucwords($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * testSubstr_count
     *
     * @param $string
     * @param $search
     * @param $expected
     * @param $caseSensitive
     *
     */
    #[DataProvider('substrCountProvider')]
    public function testSubstrCount($string, $search, $expected, $caseSensitive)
    {
        self::assertEquals($expected, Utf8String::substrCount($string, $search, $caseSensitive));
    }

    /**
     * Test...
     *
     * @param  string  $source         @todo
     * @param  string  $from_encoding  @todo
     * @param  string  $to_encoding    @todo
     * @param  string  $expect         @todo
     *
     * @return  array
     *
     */
    #[DataProvider('convertEncodingProvider')]
    public function testConvertEncoding($source, $from_encoding, $to_encoding, $expect): void
    {
        $actual = Utf8String::convertEncoding($source, $from_encoding, $to_encoding);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string  $string  @todo
     * @param  string  $expect  @todo
     *
     * @return  array
     *
     */
    #[DataProvider('isUtf8Provider')]
    public function testValid($string, $expect): void
    {
        $actual = Utf8String::isUtf8($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string  $string  @todo
     * @param  string  $expect  @todo
     *
     * @return  array
     *
     */
    #[DataProvider('unicodeToUtf8Provider')]
    public function testUnicodeToUtf8($string, $expect): void
    {
        $actual = Utf8String::unicodeToUtf8($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string  $string  @todo
     * @param  string  $expect  @todo
     *
     * @return  array
     *
     */
    #[DataProvider('unicodeToUtf16Provider')]
    public function testUnicodeToUtf16($string, $expect): void
    {
        $actual = Utf8String::unicodeToUtf16($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param  string  $string  @todo
     * @param  string  $expect  @todo
     *
     * @return  array
     *
     */
    #[DataProvider('isUtf8Provider')]
    public function testCompliant($string, $expect): void
    {
        $actual = Utf8String::compliant($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * testShuffle
     *
     * @return  void
     *
     */
    #[DataProvider('providerTestShuffle')]
    public function testShuffle($string)
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

    public function testToAscii(): void
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
