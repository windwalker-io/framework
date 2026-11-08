<?php

declare(strict_types=1);

namespace Windwalker\Scalars\Test;

use BadMethodCallException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Scalars\StringObject;
use Windwalker\Utilities\Str;
use Windwalker\Utilities\Test\MbstringTestTrait;
use Windwalker\Utilities\Utf8String;

use function Windwalker\str;

/**
 * Tests the string manipulation helpers of StringObject.
 */
class StringObjectTest extends TestCase
{
    use MbstringTestTrait;

    #[Test]
    public function strHelperCreatesStringObjectWithEncoding()
    {
        $s = str('白日依山盡', StringObject::ENCODING_US_ASCII);

        self::assertEquals('白日依山盡', $s->getString());
        self::assertEquals(StringObject::ENCODING_US_ASCII, $s->getEncoding());
    }

    #[Test]
    public function createBuildsStringObjectWithEncoding()
    {
        $s = StringObject::create('白日依山盡', StringObject::ENCODING_US_ASCII);

        self::assertEquals('白日依山盡', $s->getString());
        self::assertEquals(StringObject::ENCODING_US_ASCII, $s->getEncoding());
    }

    #[Test]
    public function withStringReturnsNewInstanceWithGivenString()
    {
        $s = new StringObject('Foo');

        self::assertEquals('Foo', $s->getString());

        $s2 = $s->withString('白日依山盡');

        self::assertNotSame($s, $s2);
        self::assertNotEquals('白日依山盡', $s->getString());
        self::assertEquals('白日依山盡', $s2->getString());
    }

    #[Test]
    public function withEncodingReturnsNewInstanceWithGivenEncoding()
    {
        $s = new StringObject();

        self::assertEquals('UTF-8', $s->getEncoding());

        $s2 = $s->withEncoding(StringObject::ENCODING_DEFAULT_ISO);

        self::assertNotSame($s, $s2);
        self::assertNotEquals(StringObject::ENCODING_DEFAULT_ISO, $s->getEncoding());
        self::assertEquals(StringObject::ENCODING_DEFAULT_ISO, $s2->getEncoding());
    }

    #[Test]
    public function callProxiesBooleanResultOfStrUtility()
    {
        self::assertEquals(
            Str::endsWith('FooBar', 'bar', false, Str::ENCODING_UTF8),
            new StringObject('FooBar', StringObject::ENCODING_UTF8)->endsWith('bar', false)
        );
    }

    #[Test]
    public function callProxiesStringResultUsingUtf8Encoding()
    {
        self::assertEquals(
            Str::slice('白日依山盡', 1, 3, Str::ENCODING_UTF8),
            new StringObject('白日依山盡', StringObject::ENCODING_UTF8)->slice(1, 3)
        );
    }

    #[Test]
    public function callProxiesStringResultUsingIsoEncoding()
    {
        self::assertEquals(
            Str::slice('白日依山盡', 1, 3, Str::ENCODING_DEFAULT_ISO),
            new StringObject('白日依山盡', StringObject::ENCODING_DEFAULT_ISO)->slice(1, 3)
        );
    }

    #[Test]
    public function callThrowsOnUnknownMethod()
    {
        $this->expectException(BadMethodCallException::class);

        new StringObject('Foo')->notexists();
    }

    #[Test]
    #[DataProvider('offsetGetProvider')]
    public function offsetGetReturnsCharacterAtOffset(int $offset, string $expected)
    {
        $s = new StringObject('白日依山盡');

        self::assertEquals($expected, (string) $s[$offset]);
    }

    public static function offsetGetProvider(): array
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

    #[Test]
    #[DataProvider('offsetSetProvider')]
    public function offsetSetReplacesCharacterAtOffset(string $string, string $replace, int $offset, string $expected)
    {
        $s = new StringObject($string);

        $s[$offset] = $replace;

        self::assertEquals($expected, (string) $s);
    }

    public static function offsetSetProvider(): array
    {
        return [
            ['Foobar', ' B', 3, 'Foo Bar'],
            ['桃之夭夭', '逃', 0, '逃之夭夭'],
            ['下筆千言', '千萬', 2, '下筆千萬言'],
        ];
    }

    #[Test]
    #[DataProvider('offsetUnsetProvider')]
    public function offsetUnsetRemovesCharacterAtOffset(string $string, int $offset, string $expected)
    {
        $s = new StringObject($string);

        unset($s[$offset]);

        self::assertEquals($expected, (string) $s);
    }

    public static function offsetUnsetProvider(): array
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
    public function offsetExistsChecksCharacterOffsets()
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

    #[Test]
    public function countReturnsNumberOfCharactersInEncoding()
    {
        self::assertCount(6, new StringObject('Foobar'));
        self::assertCount(6, new StringObject('Foobar', StringObject::ENCODING_DEFAULT_ISO));
        self::assertCount(5, new StringObject('白日依山盡'));
        self::assertCount(15, new StringObject('白日依山盡', StringObject::ENCODING_DEFAULT_ISO));
    }

    #[Test]
    public function getIteratorIteratesCharacters()
    {
        $s = new StringObject('白日依山盡');

        self::assertEquals(Utf8String::strSplit((string) $s), iterator_to_array($s));
    }

    #[Test]
    #[DataProvider('toLowerCaseProvider')]
    public function toLowerCaseReturnsNewLowercasedInstance(string $string, string $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->toLowerCase();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals((string) $s2, $expected);
    }

    public static function toLowerCaseProvider(): array
    {
        return [
            ['FooBar', 'foobar'],
            ['FÒÔBÀŘ', 'fòôbàř'],
            ['山巔一寺一壺酒', '山巔一寺一壺酒'],
        ];
    }

    #[Test]
    #[DataProvider('toUpperCaseProvider')]
    public function toUpperCaseReturnsNewUppercasedInstance(string $string, string $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->toUpperCase();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    public static function toUpperCaseProvider(): array
    {
        return [
            ['FooBar', 'FOOBAR'],
            ['FÒÔBÀŘ', 'FÒÔBÀŘ'],
            ['山巔一寺一壺酒', '山巔一寺一壺酒'],
        ];
    }

    #[Test]
    #[DataProvider('lengthProvider')]
    public function lengthReturnsCharacterCount(string $string, int $expected, ?string $encoding = null)
    {
        $s = new StringObject($string, $encoding);
        $length = $s->length();

        self::assertEquals($expected, $length);
    }

    public static function lengthProvider(): array
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

    #[Test]
    #[DataProvider('replaceProvider')]
    public function replaceSubstitutesSearchWithReplacement(string $string, $search, $replacement, $expected, int $count)
    {
        $s = new StringObject($string);
        $s2 = $s->replace($search, $replacement, $c);

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
        self::assertEquals($count, $c);
    }

    public static function replaceProvider(): array
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

    #[Test]
    #[DataProvider('strSplitProvider')]
    public function chopSplitsStringIntoChunks(string $string, int $length, $expected)
    {
        $s = new StringObject($string);

        try {
            self::assertEquals($expected, $s->chop($length)->dump());
        } catch (InvalidArgumentException $e) {
            self::assertEquals(0, $length);
        }
    }

    #[Test]
    #[DataProvider('strcmpProvider')]
    public function compareReturnsSignOfComparison(string $str1, string $str2, int $expected)
    {
        $s = new StringObject($str1);

        $actual = $s->compare($str2);

        if ($actual !== 0) {
            $actual /= abs($actual);
        }

        self::assertEquals($expected, $actual);
    }

    #[Test]
    #[DataProvider('strcasecmpProvider')]
    public function compareInsensitiveIgnoresLetterCase(string $str1, string $str2, int $expected)
    {
        $s = new StringObject($str1);

        $actual = $s->compare($str2, false);

        if ($actual !== 0) {
            $actual /= abs($actual);
        }

        self::assertEquals($expected, $actual);
    }

    #[Test]
    #[DataProvider('strrevProvider')]
    public function reverseReturnsNewReversedInstance(string $string, string $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->reverse();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    #[Test]
    #[DataProvider('substrReplaceProvider')]
    public function substrReplaceReplacesPortionOfString(
        string $expected,
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

    #[Test]
    #[DataProvider('ltrimProvider')]
    public function trimLeftRemovesLeadingCharacters(string $string, ?string $charlist, string $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->trimLeft($charlist);

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    #[Test]
    #[DataProvider('rtrimProvider')]
    public function trimRightRemovesTrailingCharacters(string $string, ?string $charlist, string $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->trimRight($charlist);

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    #[Test]
    #[DataProvider('trimProvider')]
    public function trimRemovesSurroundingCharacters(string $string, ?string $charlist, string $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->trim($charlist);

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    #[Test]
    #[DataProvider('ucfirstProvider')]
    public function upperCaseFirstCapitalizesFirstCharacter(string $string, string $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->upperCaseFirst();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    #[Test]
    #[DataProvider('lcfirstProvider')]
    public function lowerCaseFirstLowercasesFirstCharacter(string $string, string $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->lowerCaseFirst();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    #[Test]
    #[DataProvider('ucwordsProvider')]
    public function upperCaseWordsCapitalizesEachWord(string $string, string $expected)
    {
        $s = new StringObject($string);
        $s2 = $s->upperCaseWords();

        self::assertInstanceOf(StringObject::class, $s2);
        self::assertNotSame($s, $s2);
        self::assertEquals($expected, (string) $s2);
    }

    #[Test]
    #[DataProvider('substrCountProvider')]
    public function substrCountCountsOccurrences(string $string, string $search, int $expected, bool $caseSensitive)
    {
        $s = new StringObject($string);

        self::assertEquals($expected, $s->substrCount($search, $caseSensitive));
    }

    #[Test]
    #[DataProvider('indexOfProvider')]
    public function indexOfReturnsFirstOccurrencePosition(string $string, string $search, $expected)
    {
        $s = new StringObject($string);

        self::assertSame($expected, $s->indexOf($search));
    }

    public static function indexOfProvider(): array
    {
        return [
            ['FooBar', 'B', 3],
            ['FooBar', 'asd', -1],
            ['山巔一寺一壺酒', '一寺', 2],
            ['山巔一寺一壺酒', '一', 2],
            ['山巔一寺一壺酒', '山', 0],
            ['山巔一寺一壺酒', '舞扇舞', -1],
        ];
    }

    #[Test]
    #[DataProvider('indexOfLastProvider')]
    public function indexOfLastReturnsLastOccurrencePosition(string $string, string $search, $expected)
    {
        $s = new StringObject($string);

        self::assertSame($expected, $s->indexOfLast($search));
    }

    public static function indexOfLastProvider(): array
    {
        return [
            ['FooBarBaz', 'B', 6],
            ['FooBarBaz', 'asd', -1],
            ['山巔一寺一壺酒', '一寺', 2],
            ['山巔一寺一壺酒', '一', 4],
            ['山巔一寺一壺酒', '酒', 6],
            ['山巔一寺一壺酒', '舞扇舞', -1],
        ];
    }

    #[Test]
    #[DataProvider('explodeProvider')]
    public function explodeSplitsStringByDelimiter(string $string, string $delimiter, array $expected)
    {
        $s = new StringObject($string);

        self::assertEquals($expected, $s->explode($delimiter)->dump());
    }

    public static function explodeProvider(): array
    {
        return [
            ['Foo/Bar/Yoo', '/', ['Foo', 'Bar', 'Yoo']],
            ['山巔一寺一壺酒', '一', ['山巔', '寺', '壺酒']],
        ];
    }

    #[Test]
    public function splitSplitsStringByDelimiter(): void
    {
        self::assertEquals(
            ['Foo', 'Bar', 'Yoo'],
            (new StringObject('Foo/Bar/Yoo'))->split('/')->dump()
        );
    }

    #[Test]
    public function applyReturnsNewInstanceFromCallbackResult()
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

    #[Test]
    public function pipeReturnsCallbackResult()
    {
        $s = new StringObject('FooBar');

        $s2 = $s->pipe(
            static function (StringObject $str) {
                return $str->toUpperCase();
            }
        );

        self::assertNotSame($s, $s2);
        self::assertEquals('FOOBAR', (string) $s2);
    }

    #[Test]
    public function appendAddsSuffixToNewInstance(): void
    {
        $str = str('foo');
        $str2 = $str->append(' bar');

        self::assertEquals('foo bar', (string) $str2);
        self::assertNotSame($str, $str2);
    }

    #[Test]
    public function prependAddsPrefixToNewInstance(): void
    {
        $str = str('foo');
        $str2 = $str->prepend('bar ');

        self::assertEquals('bar foo', (string) $str2);
        self::assertNotSame($str, $str2);
    }

    #[Test]
    public function wrapReturnsSameInstanceWhenAlreadyStringObject(): void
    {
        $s = str('foo');

        self::assertSame($s, StringObject::wrap($s));
    }

    #[Test]
    public function wrapCastsScalarToStringObject(): void
    {
        self::assertEquals('123', (string) StringObject::wrap(123));
    }

    #[Test]
    public function toFloatCastsStringToFloat(): void
    {
        self::assertSame(1.5, str('1.5')->toFloat());
    }

    #[Test]
    public function isNullIsTrueOnlyForEmptyString(): void
    {
        self::assertTrue(str('')->isNull());
        self::assertFalse(str('0')->isNull());
    }

    #[Test]
    public function notNullIsTrueOnlyForNonEmptyString(): void
    {
        self::assertTrue(str('0')->notNull());
        self::assertFalse(str('')->notNull());
    }

    #[Test]
    public function replaceCallbackReplacesMatchesAndCountsThem(): void
    {
        $s = str('a1b2')->replaceCallback('/\d/', fn(array $m) => $m[0] * 2, -1, $count);

        self::assertEquals('a2b4', (string) $s);
        self::assertSame(2, $count);
    }

    #[Test]
    public function stripHtmlTagsRemovesTagsExceptAllowed(): void
    {
        self::assertEquals('foo bar', (string) str('<p>foo <b>bar</b></p>')->stripHtmlTags());
        self::assertEquals('foo <b>bar</b>', (string) str('<p>foo <b>bar</b></p>')->stripHtmlTags('<b>'));
    }

    #[Test]
    public function inflectorMethodsDetectAndConvertNumber(): void
    {
        self::assertTrue(str('flowers')->isPlural());
        self::assertTrue(str('flower')->isSingular());
        self::assertEquals('flowers', (string) str('flower')->toPlural());
        self::assertEquals('flower', (string) str('flowers')->toSingular());
    }
}
