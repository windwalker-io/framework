<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\String\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\String\Mbstring;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * Test class of String
 *
 * @since 2.0
 */
class MbstringTest extends TestCase
{
    use BaseAssertionTrait;

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function isAsciiProvider()
    {
        return [
            ['ascii', true],
            ['1024', true],
            ['#$#@$%', true],
            ['áÑ', false],
            ['ÿ©', false],
            ['¡¾', false],
            ['÷™', false],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strposProvider()
    {
        return [
            [3, 'missing', 'sing', 0],
            [false, 'missing', 'sting', 0],
            [4, 'missing', 'ing', 0],
            [10, ' объектов на карте с', 'на карте', 0],
            [0, 'на карте с', 'на карте', 0, 0],
            [false, 'на карте с', 'на каррте', 0],
            [false, 'на карте с', 'на карте', 2],
            [3, 'missing', 'sing', 0],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strrposProvider()
    {
        return [
            [3, 'missing', 'sing', 0],
            [false, 'missing', 'sting', 0],
            [4, 'missing', 'ing', 0],
            [10, ' объектов на карте с', 'на карте', 0],
            [0, 'на карте с', 'на карте', 0],
            [false, 'на карте с', 'на каррте', 0],
            [3, 'на карте с', 'карт', 2],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function substrProvider()
    {
        return [
            ['issauga', 'Mississauga', 4, null],
            ['на карте с', ' объектов на карте с', 10, null],
            ['на ка', ' объектов на карте с', 10, 5],
            ['те с', ' объектов на карте с', -4, null],
            [false, ' объектов на карте с', 99, null],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strtolowerProvider()
    {
        return [
            ['Windwalker! Rocks', 'windwalker! rocks'],
            ['FÒÔbàř', 'fòôbàř'],
            ['fòôbàř', 'fòôbàř'],
            ['白日依山盡', '白日依山盡'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strtoupperProvider()
    {
        return [
            ['Windwalker! Rocks', 'WINDWALKER! ROCKS'],
            ['FÒÔbàř', 'FÒÔBÀŘ'],
            ['FÒÔBÀŘ', 'FÒÔBÀŘ'],
            ['白日依山盡', '白日依山盡'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strlenProvider()
    {
        return [
            ['Windwalker! Rocks', 17],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strIreplaceProvider()
    {
        return [
            ['Pig', 'cow', 'the pig jumped', null, 'the cow jumped'],
            ['Pig', 'cow', 'the pig jumped', 1, 'the cow jumped'],
            ['Pig', 'cow', 'the pig jumped over the cow', 1, 'the cow jumped over the cow'],
            [
                ['PIG', 'JUMPED'],
                ['cow', 'hopped'],
                'the pig jumped over the pig',
                null,
                'the cow hopped over the cow',
            ],
            ['шил', 'биш', 'Би шил идэй чадна', 1, 'Би биш идэй чадна'],
            ['/', ':', '/test/slashes/', null, ':test:slashes:'],
            ['/', ':', '/test/slashes/', 1, ':test/slashes/'],
            ['', ':', '/test/slashes/', null, '/test/slashes/'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strSplitProvider()
    {
        return [
            ['string', 1, ['s', 't', 'r', 'i', 'n', 'g']],
            ['string', 2, ['st', 'ri', 'ng']],
            ['волн', 3, ['вол', 'н']],
            ['волн', 1, ['в', 'о', 'л', 'н']],
            ['волн', 0, false],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strcasecmpProvider()
    {
        return [
            ['THIS IS STRING1', 'this is string1', 0],
            ['this is string1', 'this is string2', -1],
            ['this is string2', 'this is string1', 1],
            ['бгдпт', 'бгдпт', 0],
            ['àbc', 'abc', 1],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strcmpProvider()
    {
        return [
            ['THIS IS STRING1', 'this is string1', -1],
            ['this is string1', 'this is string2', -1],
            ['this is string2', 'this is string1', 1],
            ['a', 'B', 1],
            ['A', 'b', -1],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strcspnProvider()
    {
        return [
            ['subject <a> string <a>', '<>', 0, null, 8],
            ['Би шил {123} идэй {456} чадна', '}{', 0, null, 7],
            ['Би шил {123} идэй {456} чадна', '}{', 13, 10, 5],
            ['Би шил {123} идэй {456} чадна', '', 13, 10, 0],
            ['Not contains', '}{', 13, 10, 0],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function stristrProvider()
    {
        return [
            ['haystack', 'needle', false],
            ['before match, after match', 'match', 'match, after match'],
            ['Би шил идэй чадна', 'шил', 'шил идэй чадна'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strrevProvider()
    {
        return [
            ['abc def', 'fed cba'],
            ['Би шил', 'лиш иБ'],
            ['白日依山盡', '盡山依日白'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function strspnProvider()
    {
        return [
            ['A321 Main Street', '0123456789', 1, 2, 2],
            ['321 Main Street', '0123456789', 0, 2, 2],
            ['A321 Main Street', '0123456789', 0, 10, 0],
            ['321 Main Street', '0123456789', 0, null, 3],
            ['Main Street 321', '0123456789', 0, -3, 0],
            ['321 Main Street', '0123456789', 0, -13, 2],
            ['321 Main Street', '0123456789', 0, -12, 3],
            ['A321 Main Street', '0123456789', 0, null, 0],
            ['A321 Main Street', '0123456789', 1, 10, 3],
            ['A321 Main Street', '0123456789', 1, null, 3],
            ['Би шил идэй чадна', 'Би', 0, null, 2],
            ['чадна Би шил идэй чадна', 'Би', 0, null, 0],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function substrReplaceProvider()
    {
        return [
            ['321 Broadway Avenue', '321 Main Street', 'Broadway Avenue', 4, null],
            ['321 Broadway Street', '321 Main Street', 'Broadway', 4, 4],
            ['чадна 我能吞', 'чадна Би шил идэй чадна', '我能吞', 6, null],
            ['чадна 我能吞 шил идэй чадна', 'чадна Би шил идэй чадна', '我能吞', 6, 2],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function ltrimProvider()
    {
        return [
            ['   abc def', null, 'abc def'],
            ['   abc def', '', '   abc def'],
            [' Би шил', null, 'Би шил'],
            ["\t\n\r\x0BБи шил", null, 'Би шил'],
            ["\x0B\t\n\rБи шил", "\t\n\x0B", "\rБи шил"],
            ["\x09Би шил\x0A", "\x09\x0A", "Би шил\x0A"],
            ['1234abc', '0123456789', 'abc'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function rtrimProvider()
    {
        return [
            ['abc def   ', null, 'abc def'],
            ['abc def   ', '', 'abc def   '],
            ['Би шил ', null, 'Би шил'],
            ["Би шил\t\n\r\x0B", null, 'Би шил'],
            ["Би шил\r\x0B\t\n", "\t\n\x0B", "Би шил\r"],
            ["\x09Би шил\x0A", "\x09\x0A", "\x09Би шил"],
            ['1234abc', 'abc', '01234'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function trimProvider()
    {
        return [
            ['  abc def   ', null, 'abc def'],
            ['  abc def   ', '', '  abc def   '],
            ['   Би шил ', null, 'Би шил'],
            ["\t\n\r\x0BБи шил\t\n\r\x0B", null, 'Би шил'],
            ["\x0B\t\n\rБи шил\r\x0B\t\n", "\t\n\x0B", "\rБи шил\r"],
            ["\x09Би шил\x0A", "\x09\x0A", "Би шил"],
            ['1234abc56789', '0123456789', 'abc'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function ucfirstProvider()
    {
        return [
            ['george', 'George'],
            ['мога', 'Мога'],
            ['ψυχοφθόρα', 'Ψυχοφθόρα'],
            ['', ''],
            ['ψ', 'Ψ'],
        ];
    }

    /**
     * lcfirstProvider
     *
     * @return  array
     */
    public function lcfirstProvider()
    {
        return [
            ['GEORGE', 'gEORGE'],
            ['МОГА', 'мОГА'],
            ['ΨΥΧΟΦΘΌΡΑ', 'ψΥΧΟΦΘΌΡΑ'],
            ['', ''],
            ['Ψ', 'ψ'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function ucwordsProvider()
    {
        return [
            ['george washington', 'George Washington'],
            ["george\r\nwashington", "George\r\nWashington"],
            ['мога', 'Мога'],
            ['αβγ δεζ', 'Αβγ Δεζ'],
            ['åbc öde', 'Åbc Öde'],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function convertEncodingProvider()
    {
        return [
            ['Åbc Öde €2.0', 'UTF-8', 'ISO-8859-15', "\xc5bc \xd6de \xA42.0"],
            ['', 'UTF-8', 'ISO-8859-15', ''],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function isUtf8Provider()
    {
        return [
            ["\xCF\xB0", true],
            ["\xFBa", false],
            ["\xFDa", false],
            ["foo\xF7bar", false],
            ['george Мога Ž Ψυχοφθόρα ฉันกินกระจกได้ 我能吞下玻璃而不伤身体 ', true],
            ["\xFF ABC", false],
            ["\xFa ABC", false],
            ["0xfffd ABC", true],
            ['', true],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function unicodeToUtf8Provider()
    {
        return [
            ["\u0422\u0435\u0441\u0442 \u0441\u0438\u0441\u0442\u0435\u043c\u044b", "Тест системы"],
            ["\u00dcberpr\u00fcfung der Systemumstellung", "Überprüfung der Systemumstellung"],
        ];
    }

    /**
     * Test...
     *
     * @return  array
     *
     * @since   2.0
     */
    public function unicodeToUtf16Provider()
    {
        return [
            ["\u0422\u0435\u0441\u0442 \u0441\u0438\u0441\u0442\u0435\u043c\u044b", "Тест системы"],
            ["\u00dcberpr\u00fcfung der Systemumstellung", "Überprüfung der Systemumstellung"],
        ];
    }

    /**
     * testCallStatic
     *
     * @return  void
     */
    public function testCallStatic()
    {
        $this->expectException(\BadMethodCallException::class);

        Mbstring::noexists('test');
    }

    /**
     * Test...
     *
     * @param   string  $string   @todo
     * @param   boolean $expected @todo
     *
     * @return  void
     *
     * @dataProvider  isAsciiProvider
     * @since         2.0
     */
    public function testIsAscii($string, $expected)
    {
        $this->assertEquals(
            $expected,
            Mbstring::isAscii($string)
        );
    }

    /**
     * Test...
     *
     * @param   string  $expect   @todo
     * @param   string  $haystack @todo
     * @param   string  $needle   @todo
     * @param   integer $offset   @todo
     *
     * @return  void
     *
     * @dataProvider  strposProvider
     * @since         2.0
     */
    public function testStrpos($expect, $haystack, $needle, $offset = 0)
    {
        $actual = Mbstring::strpos($haystack, $needle, $offset);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string  $expect   @todo
     * @param   string  $haystack @todo
     * @param   string  $needle   @todo
     * @param   integer $offset   @todo
     *
     * @return  array
     *
     * @dataProvider  strrposProvider
     * @since         2.0
     */
    public function testStrrpos($expect, $haystack, $needle, $offset = 0)
    {
        $actual = Mbstring::strrpos($haystack, $needle, $offset);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string   $expect @todo
     * @param   string   $string @todo
     * @param   string   $start  @todo
     * @param   bool|int $length @todo
     *
     * @return  array
     *
     * @dataProvider  substrProvider
     * @since         2.0
     */
    public function testSubstr($expect, $string, $start, $length = null)
    {
        $actual = Mbstring::substr($string, $start, $length);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $string @todo
     * @param   string $expect @todo
     *
     * @return  array
     *
     * @dataProvider  strtolowerProvider
     * @since         2.0
     */
    public function testStrtolower($string, $expect)
    {
        $actual = Mbstring::strtolower($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $string @todo
     * @param   string $expect @todo
     *
     * @return  array
     *
     * @dataProvider  strtoupperProvider
     * @since         2.0
     */
    public function testStrtoupper($string, $expect)
    {
        $actual = Mbstring::strtoupper($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $string @todo
     * @param   string $expect @todo
     *
     * @return  array
     *
     * @dataProvider  strlenProvider
     * @since         2.0
     */
    public function testStrlen($string, $expect)
    {
        $actual = Mbstring::strlen($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string  $search  @todo
     * @param   string  $replace @todo
     * @param   string  $subject @todo
     * @param   integer $count   @todo
     * @param   string  $expect  @todo
     *
     * @return  array
     *
     * @dataProvider  strIreplaceProvider
     * @since         2.0
     */
    public function testStr_ireplace($search, $replace, $subject, $count, $expect)
    {
        $actual = Mbstring::strIreplace($search, $replace, $subject, $count);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $string       @todo
     * @param   int    $split_length @todo
     * @param   string $expect       @todo
     *
     * @return  array
     *
     * @dataProvider  strSplitProvider
     * @since         2.0
     */
    public function testStr_split($string, $split_length, $expect)
    {
        $actual = Mbstring::strSplit($string, $split_length);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $string1 @todo
     * @param   string $string2 @todo
     * @param   string $locale  @todo
     * @param   string $expect  @todo
     *
     * @return  array
     *
     * @dataProvider  strcasecmpProvider
     * @since         2.0
     */
    public function testStrcasecmp($string1, $string2, $expect)
    {
        $actual = Mbstring::strcasecmp($string1, $string2);

        if ($actual !== 0) {
            $actual /= abs($actual);
        }

        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $string1 @todo
     * @param   string $string2 @todo
     * @param   string $locale  @todo
     * @param   string $expect  @todo
     *
     * @return  array
     *
     * @dataProvider  strcmpProvider
     * @since         2.0
     */
    public function testStrcmp($string1, $string2, $expect)
    {
        $actual = Mbstring::strcmp($string1, $string2);

        if ($actual !== 0) {
            $actual /= abs($actual);
        }

        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string  $haystack @todo
     * @param   string  $needles  @todo
     * @param   integer $start    @todo
     * @param   integer $len      @todo
     * @param   string  $expect   @todo
     *
     * @return  array
     *
     * @dataProvider  strcspnProvider
     * @since         2.0
     */
    public function testStrcspn($haystack, $needles, $start, $len, $expect)
    {
        $actual = Mbstring::strcspn($haystack, $needles, $start, $len);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $haystack @todo
     * @param   string $needle   @todo
     * @param   string $expect   @todo
     *
     * @return  array
     *
     * @dataProvider  stristrProvider
     * @since         2.0
     */
    public function testStristr($haystack, $needle, $expect)
    {
        $actual = Mbstring::stristr($haystack, $needle);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $string @todo
     * @param   string $expect @todo
     *
     * @return  array
     *
     * @dataProvider  strrevProvider
     * @since         2.0
     */
    public function testStrrev($string, $expect)
    {
        $actual = Mbstring::strrev($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string  $subject @todo
     * @param   string  $mask    @todo
     * @param   integer $start   @todo
     * @param   integer $length  @todo
     * @param   string  $expect  @todo
     *
     * @return  array
     *
     * @dataProvider  strspnProvider
     * @since         2.0
     */
    public function testStrspn($subject, $mask, $start, $length, $expect)
    {
        $actual = Mbstring::strspn($subject, $mask, $start, $length);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string  $expect      @todo
     * @param   string  $string      @todo
     * @param   string  $replacement @todo
     * @param   integer $start       @todo
     * @param   integer $length      @todo
     *
     * @return  array
     *
     * @dataProvider  substrReplaceProvider
     * @since         2.0
     */
    public function testSubstrReplace($expect, $string, $replacement, $start, $length)
    {
        $actual = Mbstring::substrReplace($string, $replacement, $start, $length);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $string   @todo
     * @param   string $charlist @todo
     * @param   string $expect   @todo
     *
     * @return  array
     *
     * @dataProvider  ltrimProvider
     * @since         2.0
     */
    public function testLtrim($string, $charlist, $expect)
    {
        if ($charlist === null) {
            $actual = Mbstring::ltrim($string);
        } else {
            $actual = Mbstring::ltrim($string, $charlist);
        }

        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $string   @todo
     * @param   string $charlist @todo
     * @param   string $expect   @todo
     *
     * @return  array
     *
     * @dataProvider  rtrimProvider
     * @since         2.0
     */
    public function testRtrim($string, $charlist, $expect)
    {
        if ($charlist === null) {
            $actual = Mbstring::rtrim($string);
        } else {
            $actual = Mbstring::rtrim($string, $charlist);
        }

        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $string   @todo
     * @param   string $charlist @todo
     * @param   string $expect   @todo
     *
     * @return  array
     *
     * @dataProvider  trimProvider
     * @since         2.0
     */
    public function testTrim($string, $charlist, $expect)
    {
        if ($charlist === null) {
            $actual = Mbstring::trim($string);
        } else {
            $actual = Mbstring::trim($string, $charlist);
        }

        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $string @todo
     * @param   string $expect @todo
     *
     * @return  array
     *
     * @dataProvider  ucfirstProvider
     * @since         2.0
     */
    public function testUcfirst($string, $expect)
    {
        $actual = Mbstring::ucfirst($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * testLcfirst
     *
     * @param string $string
     * @param string $expect
     *
     * @return  void
     *
     * @dataProvider  lcfirstProvider
     */
    public function testLcfirst($string, $expect)
    {
        $actual = Mbstring::lcfirst($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $string @todo
     * @param   string $expect @todo
     *
     * @return  array
     *
     * @dataProvider  ucwordsProvider
     * @since         2.0
     */
    public function testUcwords($string, $expect)
    {
        $actual = Mbstring::ucwords($string);
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
     * @dataProvider substrCountProvider
     */
    public function testSubstrCount($string, $search, $expected, $caseSensitive)
    {
        self::assertEquals($expected, Mbstring::substrCount($string, $search, $caseSensitive));
    }

    /**
     * substr_countProvider
     *
     * @return  array
     */
    public function substrCountProvider()
    {
        return [
            ['FooBarFlowerSakura', 'Flower', 1, Mbstring::CASE_SENSITIVE],
            ['FooBarFlowerSakura', 'o', 3, Mbstring::CASE_SENSITIVE],
            ['FooOOooo', 'o', 5, Mbstring::CASE_SENSITIVE],
            ['FooOOooo', 'o', 7, Mbstring::CASE_INSENSITIVE],
            ['FÒÔòôòô', 'ô', 2, Mbstring::CASE_SENSITIVE],
            ['FÒÔòôòô', 'ô', 3, Mbstring::CASE_INSENSITIVE],
            ['объектов на карте с', 'б', 1, Mbstring::CASE_SENSITIVE],
            ['庭院深深深幾許', '深', 3, Mbstring::CASE_SENSITIVE],
        ];
    }

    /**
     * Test...
     *
     * @param   string $source        @todo
     * @param   string $from_encoding @todo
     * @param   string $to_encoding   @todo
     * @param   string $expect        @todo
     *
     * @return  array
     *
     * @dataProvider  convertEncodingProvider
     * @since         2.0
     */
    public function testConvertEncoding($source, $from_encoding, $to_encoding, $expect)
    {
        $actual = Mbstring::convertEncoding($source, $from_encoding, $to_encoding);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $string @todo
     * @param   string $expect @todo
     *
     * @return  array
     *
     * @dataProvider  isUtf8Provider
     * @since         2.0
     */
    public function testValid($string, $expect)
    {
        $actual = Mbstring::isUtf8($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $string @todo
     * @param   string $expect @todo
     *
     * @return  array
     *
     * @dataProvider  unicodeToUtf8Provider
     * @since         2.0
     */
    public function testUnicodeToUtf8($string, $expect)
    {
        $actual = Mbstring::unicodeToUtf8($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $string @todo
     * @param   string $expect @todo
     *
     * @return  array
     *
     * @dataProvider  unicodeToUtf16Provider
     * @since         2.0
     */
    public function testUnicodeToUtf16($string, $expect)
    {
        $actual = Mbstring::unicodeToUtf16($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Test...
     *
     * @param   string $string @todo
     * @param   string $expect @todo
     *
     * @return  array
     *
     * @dataProvider  isUtf8Provider
     * @since         2.0
     */
    public function testCompliant($string, $expect)
    {
        $actual = Mbstring::compliant($string);
        $this->assertEquals($expect, $actual);
    }

    /**
     * testShuffle
     *
     * @return  void
     *
     * @dataProvider providerTestShuffle
     */
    public function testShuffle($string)
    {
        $result = Mbstring::shuffle($string);

        self::assertNotEquals($result, $string);
        self::assertEquals(strlen($result), strlen($string));

        $len = mb_strlen($string);

        for ($i = 0; $i < $len; $i++) {
            $char        = mb_substr($string, $i, 1);
            $countBefore = mb_substr_count($string, $char);
            $countAfter  = mb_substr_count($result, $char);

            self::assertEquals($countBefore, $countAfter);
        }
    }

    /**
     * providerTestShuffle
     *
     * @return  array
     */
    public function providerTestShuffle()
    {
        return [
            ['foo bar'],
            ['∂∆ ˚åß'],
            ['å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬'],
        ];
    }
}
