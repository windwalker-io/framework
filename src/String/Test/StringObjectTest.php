<?php
/**
 * Part of ww4 project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT.
 * @license    Please see LICENSE file.
 */

namespace Windwalker\String\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\String\Mbstring;
use Windwalker\String\Str;
use Windwalker\String\StringObject;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * The StringObjectTest class.
 *
 * @since  3.2
 */
class StringObjectTest extends TestCase
{
    use BaseAssertionTrait;

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
    }

    /**
     * testFunctionCreate
     *
     * @return  void
     */
    public function testFunctionCreate()
    {
        $s = str('白日依山盡', StringObject::ENCODING_US_ASCII);

        self::assertInstanceOf(StringObject::class, $s);
        self::assertEquals('白日依山盡', $s->getString());
        self::assertEquals(StringObject::ENCODING_US_ASCII, $s->getEncoding());
    }

    /**
     * testStaticCreate
     *
     * @return  void
     */
    public function testStaticCreate()
    {
        $s = StringObject::create('白日依山盡', StringObject::ENCODING_US_ASCII);

        self::assertInstanceOf(StringObject::class, $s);
        self::assertEquals('白日依山盡', $s->getString());
        self::assertEquals(StringObject::ENCODING_US_ASCII, $s->getEncoding());

        $ss = StringObject::fromArray(['A', 'B', 'C']);

        self::assertTrue(is_array($ss));

        foreach ($ss as &$sv) {
            self::assertInstanceOf(StringObject::class, $sv);

            $sv = $sv->__toString();
        }

        self::assertEquals(['A', 'B', 'C'], $ss);
    }

    /**
     * testStringAccess
     *
     * @return  void
     */
    public function testStringAccess()
    {
        $s = new StringObject('Foo');

        self::assertEquals('Foo', $s->getString());

        $s2 = $s->withString('白日依山盡');

        self::assertNotSame($s, $s2);
        self::assertNotEquals('白日依山盡', $s->getString());
        self::assertEquals('白日依山盡', $s2->getString());
    }

    /**
     * testEncodingAccess
     *
     * @return  void
     */
    public function testEncodingAccess()
    {
        $s = new StringObject();

        self::assertEquals('UTF-8', $s->getEncoding());

        $s2 = $s->withEncoding(StringObject::ENCODING_DEFAULT_ISO);

        self::assertNotSame($s, $s2);
        self::assertNotEquals(StringObject::ENCODING_DEFAULT_ISO, $s->getEncoding());
        self::assertEquals(StringObject::ENCODING_DEFAULT_ISO, $s2->getEncoding());
    }

    /**
     * testCallProxy
     *
     * @return  void
     */
    public function testCallProxy()
    {
        // Test return bool
        self::assertEquals(
            Str::endsWith('FooBar', 'bar', false, Str::ENCODING_UTF8),
            (new StringObject('FooBar', StringObject::ENCODING_UTF8))->endsWith('bar', false)
        );

        // Test return string
        self::assertEquals(
            Str::slice('白日依山盡', 1, 3, Str::ENCODING_UTF8),
            (new StringObject('白日依山盡', StringObject::ENCODING_UTF8))->slice(1, 3)
        );

        self::assertEquals(
            Str::slice('白日依山盡', 1, 3, Str::ENCODING_DEFAULT_ISO),
            (new StringObject('白日依山盡', StringObject::ENCODING_DEFAULT_ISO))->slice(1, 3)
        );

        $this->legacyExpectException(\BadMethodCallException::class);

        (new StringObject('Foo'))->notexists();
    }

    /**
     * testOffsetGet
     *
     * @param     $expected
     * @param int $offset
     *
     * @dataProvider offsetGetProvider
     */
    public function testOffsetGet($offset, $expected)
    {
        $s = new StringObject('白日依山盡');

        self::assertEquals($expected, (string) $s[$offset]);
    }

    /**
     * offsetGetProvider
     *
     * @return  array
     */
    public function offsetGetProvider()
    {
        return [
            [0, '白'],
            [3, '山'],
            [10, ''],
            [-1, '盡'],
            [-3, '依'],
            [-16, ''],
        ];
    }

    /**
     * testOffsetSet
     *
     * @param     $string
     * @param     $replace
     * @param int $offset
     * @param     $expected
     *
     * @return  void
     *
     * @dataProvider offsetSetProvider
     */
    public function testOffsetSet($string, $replace, $offset, $expected)
    {
        $s = new StringObject($string);

        $s[$offset] = $replace;

        self::assertEquals($expected, (string) $s);
    }

    /**
     * offsetSetProvider
     *
     * @return  array
     */
    public function offsetSetProvider()
    {
        return [
            ['Foobar', ' B', 3, 'Foo Bar'],
            ['桃之夭夭', '逃', 0, '逃之夭夭'],
            ['下筆千言', '千萬', 2, '下筆千萬言'],
        ];
    }

    /**
     * testOffsetUnset
     *
     * @param     $string
     * @param int $offset
     * @param     $expected
     *
     * @return  void
     *
     * @dataProvider offsetUnsetProvider
     */
    public function testOffsetUnset($string, $offset, $expected)
    {
        $s = new StringObject($string);

        unset($s[$offset]);

        self::assertEquals($expected, (string) $s);
    }

    /**
     * offsetUnsetProvider
     *
     * @return  array
     */
    public function offsetUnsetProvider()
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

    /**
     * testOffsetExists
     *
     * @return  void
     */
    public function testOffsetExists()
    {
        $s = new StringObject('白日依山盡');

        self::assertTrue(isset($s[0]));
        self::assertTrue(isset($s[3]));
        self::assertTrue(isset($s[4]));
        self::assertFalse(isset($s[5]));
        self::assertTrue(isset($s[-5]));
        self::assertFalse(isset($s[9]));
        self::assertTrue(isset($s[-1]));
        self::assertTrue(isset($s[-3]));
        self::assertFalse(isset($s[-9]));
    }

    /**
     * testCount
     *
     * @return  void
     */
    public function testCount()
    {
        self::assertCount(6, new StringObject('Foobar'));
        self::assertCount(6, new StringObject('Foobar', StringObject::ENCODING_DEFAULT_ISO));
        self::assertCount(5, new StringObject('白日依山盡'));
        self::assertCount(15, new StringObject('白日依山盡', StringObject::ENCODING_DEFAULT_ISO));
    }

    /**
     * testGetIterator
     *
     * @return  void
     */
    public function testGetIterator()
    {
        $s = new StringObject('白日依山盡');

        self::assertEquals(Mbstring::strSplit((string) $s), iterator_to_array($s));
    }

    /**
     * testToLowerCase
     *
     * @param $string
     * @param $expected
     *
     * @return  void
     *
     * @dataProvider toLowerCaseProvider
     */
    public function testToLowerCase($string, $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->toLowerCase();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals((string) $s2, $expected);
    }

    /**
     * toLowerCaseProvider
     *
     * @return  array
     */
    public function toLowerCaseProvider()
    {
        return [
            ['FooBar', 'foobar'],
            ['FÒÔBÀŘ', 'fòôbàř'],
            ['山巔一寺一壺酒', '山巔一寺一壺酒'],
        ];
    }

    /**
     * testToUpperCase
     *
     * @param $string
     * @param $expected
     *
     * @return  void
     *
     * @dataProvider toUpperCaseProvider
     */
    public function testToUpperCase($string, $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->toUpperCase();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * toUpperCaseProvider
     *
     * @return  array
     */
    public function toUpperCaseProvider()
    {
        return [
            ['FooBar', 'FOOBAR'],
            ['FÒÔBÀŘ', 'FÒÔBÀŘ'],
            ['山巔一寺一壺酒', '山巔一寺一壺酒'],
        ];
    }

    /**
     * testLength
     *
     * @param             $string
     * @param int         $expected
     * @param string|null $encoding
     *
     * @return  void
     *
     * @dataProvider lengthProvider
     */
    public function testLength($string, $expected, $encoding = null)
    {
        $s = new StringObject($string, $encoding);
        $length = $s->length();

        self::assertEquals($expected, $length);
    }

    /**
     * lengthProvider
     *
     * @return  array
     */
    public function lengthProvider()
    {
        return [
            ['Foo Bar', 7],
            ['', 0],
            ['FÒÔ BÀŘ', 7],
            ['FÒÔ BÀŘ', 11, StringObject::ENCODING_DEFAULT_ISO],
            ['山巔一寺一壺酒', 7],
            ['山巔一寺一壺酒', 21, StringObject::ENCODING_DEFAULT_ISO],
            ['山巔一寺一壺酒', 21, StringObject::ENCODING_US_ASCII],
            ['山巔一寺一壺酒 二柳舞扇舞 把酒棄舊山 惡善百世流', 25],
            ['山巔一寺一壺酒 二柳舞扇舞 把酒棄舊山 惡善百世流', 69, StringObject::ENCODING_DEFAULT_ISO],
        ];
    }

    /**
     * testReplace
     *
     * @param        $string
     * @param        $search
     * @param        $replacement
     * @param        $expected
     * @param int    $count
     *
     * @return  void
     *
     * @dataProvider replaceProvider
     */
    public function testReplace($string, $search, $replacement, $expected, $count)
    {
        $s = new StringObject($string);
        $s2 = $s->replace($search, $replacement, $c);

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
        self::assertEquals($count, $c);
    }

    /**
     * replaceProvider
     *
     * @return  array
     */
    public function replaceProvider()
    {
        return [
            ['Foobar', 'oo', 'ii', 'Fiibar', 1],
            ['FlowerSakuraFlowerSakuraFlowerSakura', 'Sakura', 'Olive', 'FlowerOliveFlowerOliveFlowerOlive', 3],
            ['庭院深深深幾許', '深', '_', '庭院___幾許', 3],

            [
                'Foobar',
                ['o', 'r'],
                'i',
                'Fiibai',
                3,
            ],
            [
                'Foobar',
                ['o', 'r'],
                ['i', 'k'],
                'Fiibak',
                3,
            ],
        ];
    }

    /**
     * testChop
     *
     * @param       $string
     * @param int   $length
     * @param array $expected
     *
     * @dataProvider \Windwalker\String\Test\MbstringTest::strSplitProvider
     */
    public function testChop($string, $length, $expected)
    {
        $s = new StringObject($string);

        self::assertEquals($expected, $s->chop($length));
    }

    /**
     * testCompare
     *
     * @param     $str1
     * @param     $str2
     * @param int $expected
     *
     * @dataProvider \Windwalker\String\Test\MbstringTest::strcmpProvider
     */
    public function testCompare($str1, $str2, $expected)
    {
        $s = new StringObject($str1);

        $actual = $s->compare($str2);

        if ($actual !== 0) {
            $actual /= abs($actual);
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * testCompare
     *
     * @param     $str1
     * @param     $str2
     * @param int $expected
     *
     * @dataProvider \Windwalker\String\Test\MbstringTest::strcasecmpProvider
     */
    public function testCompareInsensitive($str1, $str2, $expected)
    {
        $s = new StringObject($str1);

        $actual = $s->compare($str2, false);

        if ($actual !== 0) {
            $actual /= abs($actual);
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * testReverse
     *
     * @param $string
     * @param $expected
     *
     * @dataProvider \Windwalker\String\Test\MbstringTest::strrevProvider
     */
    public function testReverse($string, $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->reverse();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * testSubstrReplace
     *
     * @param       $expected
     * @param mixed $string
     * @param mixed $replace
     * @param int   $start
     * @param int   $offset
     *
     * @dataProvider \Windwalker\String\Test\MbstringTest::substrReplaceProvider
     */
    public function testSubstrReplace(
        $expected,
        $string,
        $replace,
        $start = null,
        $offset = null
    ) {
        if (is_array($string)) {
            self::markTestSkipped('StringObject only test string.');

            return;
        }

        $s = new StringObject($string);
        $s2 = $s->substrReplace($replace, $start, $offset);

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * testLtrim
     *
     * @param      $string
     * @param null|$charlist
     * @param      $expected
     *
     * @dataProvider \Windwalker\String\Test\MbstringTest::ltrimProvider
     */
    public function testTrimLeft($string, $charlist, $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->trimLeft($charlist);

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * testRtrim
     *
     * @param      $string
     * @param null|$charlist
     * @param      $expected
     *
     * @return  void
     *
     * @dataProvider \Windwalker\String\Test\MbstringTest::rtrimProvider
     */
    public function testTrimRight($string, $charlist, $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->trimRight($charlist);

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * testTrim
     *
     * @param      $string
     * @param null|$charlist
     * @param      $expected
     *
     * @return  void
     *
     * @dataProvider \Windwalker\String\Test\MbstringTest::trimProvider
     */
    public function testTrim($string, $charlist, $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->trim($charlist);

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * testUcfirst
     *
     * @param $string
     * @param $expected
     *
     * @dataProvider \Windwalker\String\Test\MbstringTest::ucfirstProvider
     */
    public function testUpperCaseFirst($string, $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->upperCaseFirst();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * testLcfirst
     *
     * @param $string
     * @param $expected
     *
     * @dataProvider \Windwalker\String\Test\MbstringTest::lcfirstProvider
     */
    public function testLowerCaseFirst($string, $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->lowerCaseFirst();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * testUpperCaseWords
     *
     * @param $string
     * @param $expected
     *
     * @return  void
     *
     * @dataProvider \Windwalker\String\Test\MbstringTest::ucwordsProvider
     */
    public function testUpperCaseWords($string, $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->upperCaseWords();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    /**
     * testSubstrCount
     *
     * @param      $string
     * @param      $search
     * @param int  $expected
     * @param bool $caseSensitive
     *
     * @dataProvider \Windwalker\String\Test\MbstringTest::substrCountProvider
     */
    public function testSubstrCount($string, $search, $expected, $caseSensitive)
    {
        $s = new StringObject($string);

        self::assertEquals($expected, $s->substrCount($search, $caseSensitive));
    }

    /**
     * testIndexOf
     *
     * @param     $string
     * @param     $search
     * @param int $expected
     *
     * @return  void
     *
     * @dataProvider indexOfProvider
     */
    public function testIndexOf($string, $search, $expected)
    {
        $s = new StringObject($string);

        self::assertSame($expected, $s->indexOf($search));
    }

    /**
     * indexOfProvider
     *
     * @return  array
     */
    public function indexOfProvider()
    {
        return [
            ['FooBar', 'B', 3],
            ['FooBar', 'asd', false],
            ['山巔一寺一壺酒', '一寺', 2],
            ['山巔一寺一壺酒', '一', 2],
            ['山巔一寺一壺酒', '山', 0],
            ['山巔一寺一壺酒', '舞扇舞', false],
        ];
    }

    /**
     * testIndexOf
     *
     * @param     $string
     * @param     $search
     * @param int $expected
     *
     * @return  void
     *
     * @dataProvider indexOfLastProvider
     */
    public function testIndexOfLast($string, $search, $expected)
    {
        $s = new StringObject($string);

        self::assertSame($expected, $s->indexOfLast($search));
    }

    /**
     * indexOfProvider
     *
     * @return  array
     */
    public function indexOfLastProvider()
    {
        return [
            ['FooBarBaz', 'B', 6],
            ['FooBarBaz', 'asd', false],
            ['山巔一寺一壺酒', '一寺', 2],
            ['山巔一寺一壺酒', '一', 4],
            ['山巔一寺一壺酒', '酒', 6],
            ['山巔一寺一壺酒', '舞扇舞', false],
        ];
    }

    /**
     * testExplode
     *
     * @param       $string
     * @param       $delimiter
     * @param array $expected
     *
     * @return  void
     *
     * @dataProvider explodeProvider
     */
    public function testExplode($string, $delimiter, array $expected)
    {
        $s = new StringObject($string);

        self::assertEquals($expected, $s->explode($delimiter));
    }

    /**
     * explodeProvider
     *
     * @return  array
     */
    public function explodeProvider()
    {
        return [
            ['Foo/Bar/Yoo', '/', ['Foo', 'Bar', 'Yoo']],
            ['山巔一寺一壺酒', '一', ['山巔', '寺', '壺酒']],
        ];
    }

    /**
     * testApply
     *
     * @return  void
     */
    public function testApply()
    {
        $s = new StringObject('FooBar');

        $s2 = $s->apply(
            function ($string) {
                return strtoupper($string);
            }
        );

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals('FOOBAR', (string) $s2);
    }
}
