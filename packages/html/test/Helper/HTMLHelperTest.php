<?php

declare(strict_types=1);

namespace Windwalker\Html\Test\Helper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Windwalker\Html\Helper\HTMLHelper;
use Windwalker\Utilities\Wrapper\RawWrapper;

/**
 * The HTMLHelperTest class.
 */
class HTMLHelperTest extends TestCase
{
    #[Test]
    #[DataProvider('repairProvider')]
    public function repairClosesUnclosedTags(string $html, string $expected): void
    {
        self::assertSame($expected, HTMLHelper::repair($html, false));
    }

    public static function repairProvider(): array
    {
        return [
            'balanced html is untouched' => ['<p>Hello</p>', '<p>Hello</p>'],
            'closes nested tags in reverse order' => [
                '<div><span>Hello',
                '<div><span>Hello</span></div>',
            ],
            'does not close single tags' => [
                '<p>Hello<br><img src="a.png">',
                '<p>Hello<br><img src="a.png"></p>',
            ],
        ];
    }

    #[Test]
    #[DataProvider('jsObjectProvider')]
    public function getJSObjectConvertsValues(mixed $data, bool $quoteKey, string $expected): void
    {
        self::assertSame($expected, HTMLHelper::getJSObject($data, $quoteKey));
    }

    public static function jsObjectProvider(): array
    {
        $object = new stdClass();
        $object->foo = 'bar';
        $object->num = 5;

        return [
            'null' => [null, false, 'null'],
            'true' => [true, false, 'true'],
            'false' => [false, false, 'false'],
            'integer' => [123, false, '123'],
            'float' => [1.5, false, '1.5'],
            'string is json encoded' => ['a"b', false, '"a\"b"'],
            'raw wrapper is output as is' => [new RawWrapper('function() {}'), false, 'function() {}'],
            'list array' => [[1, 'a', true], false, '[1,"a",true]'],
            'assoc array without quoted keys' => [['a' => 1, 'b' => 'x'], false, '{a:1,b:"x"}'],
            'assoc array with quoted keys' => [['a' => 1, 'b' => 'x'], true, '{"a":1,"b":"x"}'],
            'object' => [$object, false, '{foo:"bar",num:5}'],
            'nested structures' => [
                ['list' => [1, 2], 'raw' => new RawWrapper('fn')],
                false,
                '{list:[1,2],raw:fn}',
            ],
        ];
    }
}
