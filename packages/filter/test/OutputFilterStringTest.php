<?php

declare(strict_types=1);

namespace Windwalker\Filter\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Filter\OutputFilter;

/**
 * @see OutputFilter
 */
class OutputFilterStringTest extends TestCase
{
    #[Test]
    #[DataProvider('outputFilterProvider')]
    public function outputFilterTransformsStringAsExpected(string $method, string $input, string $expected): void
    {
        self::assertSame($expected, OutputFilter::$method($input));
    }

    public static function outputFilterProvider(): array
    {
        return [
            'linkXHTMLSafe escapes ampersand in href' => [
                'linkXHTMLSafe',
                '<a href="a.php?x=1&y=2">A</a>',
                '<a href="a.php?x=1&amp;y=2">A</a>',
            ],
            'linkXHTMLSafe keeps existing entity' => [
                'linkXHTMLSafe',
                '<a href="a.php?x=1&amp;y=2">A</a>',
                '<a href="a.php?x=1&amp;y=2">A</a>',
            ],
            'stringURLSafe slugifies' => [
                'stringURLSafe',
                '  Hello -- World! Café ',
                'hello-world-cafe',
            ],
            'stringURLUnicodeSlug keeps unicode' => [
                'stringURLUnicodeSlug',
                'Hello? 中文　測試 (foo)',
                'hello-中文-測試-foo',
            ],
            'ampReplace escapes bare ampersand' => [
                'ampReplace',
                'a & b &amp; c &#169; &copy; &&',
                'a &amp; b &amp; c &#169; &copy; &&',
            ],
            'cleanText strips markup and scripts' => [
                'cleanText',
                '<script>alert(1)</script><p>Hi&nbsp;<a href="http://x.com">link</a> {code}</p>',
                'Hi link (http://x.com) ',
            ],
            'stripImages' => ['stripImages', 'a<img src="x">b', 'ab'],
            'stripIframes' => ['stripIframes', 'a<iframe src="x"></iframe>b', 'ab'],
            'stripScript' => ['stripScript', "a<script>\nx()\n</script>b", 'ab'],
            'stripStyle' => ['stripStyle', 'a<style>p{}</style>b', 'ab'],
            'stripLinks' => ['stripLinks', 'a<link rel="x" href="y">b', 'ab'],
        ];
    }
}
