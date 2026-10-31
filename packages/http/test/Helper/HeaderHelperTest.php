<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Helper;

use ArrayIterator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Helper\HeaderHelper;
use Windwalker\Http\Response\Response;

class HeaderHelperTest extends TestCase
{
    #[Test]
    public function getValueFindsExactKeyAndImplodesArrays(): void
    {
        $headers = ['Accept' => ['text/html', 'application/json'], 'X-Single' => 'one'];

        self::assertSame('text/html, application/json', HeaderHelper::getValue($headers, 'Accept'));
        self::assertSame('one', HeaderHelper::getValue($headers, 'X-Single'));
    }

    #[Test]
    public function getValueFallsBackToCaseInsensitiveSearchThenDefault(): void
    {
        $headers = ['Content-Type' => 'text/plain', 'X-List' => ['a', 'b']];

        self::assertSame('text/plain', HeaderHelper::getValue($headers, 'content-type'));
        self::assertSame('a, b', HeaderHelper::getValue($headers, 'x-LIST'));
        self::assertSame('fallback', HeaderHelper::getValue($headers, 'missing', 'fallback'));
        self::assertNull(HeaderHelper::getValue($headers, 'missing'));
    }

    #[Test]
    #[DataProvider('headerNameProvider')]
    public function validatesHeaderNames(mixed $name, bool $expected): void
    {
        self::assertSame($expected, HeaderHelper::isValidName($name));
    }

    public static function headerNameProvider(): array
    {
        return [
            'simple' => ['Content-Type', true],
            'special tokens' => ["X_Foo.Bar~1!#\$%&'*+^`|", true],
            'with space' => ['Content Type', false],
            'with colon' => ['Content:Type', false],
            'empty' => ['', false],
            'with newline' => ["Foo\nBar", false],
        ];
    }

    #[Test]
    #[DataProvider('headerValueProvider')]
    public function validatesHeaderValues(mixed $value, bool $expected): void
    {
        self::assertSame($expected, HeaderHelper::isValidValue($value));
    }

    public static function headerValueProvider(): array
    {
        return [
            'plain' => ['text/html; charset=utf-8', true],
            'integer' => [123, true],
            'folded line with space' => ["foo\r\n bar", true],
            'folded line with tab' => ["foo\r\n\tbar", true],
            'bare lf' => ["foo\nbar", false],
            'bare cr' => ["foo\rbar", false],
            'crlf without continuation' => ["foo\r\nbar", false],
            'null byte' => ["foo\0bar", false],
            'del char' => ["foo\x7Fbar", false],
            'control char' => ["foo\x01bar", false],
        ];
    }

    #[Test]
    #[DataProvider('filterProvider')]
    public function filtersUnsafeCharactersFromValues(mixed $value, string $expected): void
    {
        self::assertSame($expected, HeaderHelper::filter($value));
    }

    public static function filterProvider(): array
    {
        return [
            'clean value untouched' => ['foo bar', 'foo bar'],
            'casts non strings' => [42, '42'],
            'removes control chars and del' => ["fo\x01o\x7Fbar", 'foobar'],
            'removes null byte' => ["foo\0bar", 'foobar'],
            'keeps tab' => ["foo\tbar", "foo\tbar"],
            'keeps valid continuation' => ["foo\r\n bar", "foo\r\n bar"],
            'drops cr without lf' => ["foo\rXbar", 'fooXbar'],
            'drops trailing cr' => ["foo\r", 'foo'],
            'drops cr lf at end' => ["foo\r\n", 'foo'],
            'drops split attack' => ["foo\r\nSet-Cookie: x", 'fooSet-Cookie: x'],
        ];
    }

    #[Test]
    #[DataProvider('protocolVersionProvider')]
    public function validatesProtocolVersion(mixed $version, bool $expected): void
    {
        self::assertSame($expected, HeaderHelper::isValidProtocolVersion($version));
    }

    public static function protocolVersionProvider(): array
    {
        return [
            ['1.0', true],
            ['1.1', true],
            ['2', true],
            ['2.0', false],
            ['3', false],
            ['', false],
            [1.1, false],
            [null, false],
        ];
    }

    #[Test]
    public function allToArrayConvertsScalarsTraversablesAndObjects(): void
    {
        self::assertSame(['foo'], HeaderHelper::allToArray('foo'));
        self::assertSame(['a' => 'b'], HeaderHelper::allToArray(new ArrayIterator(['a' => 'b'])));
        self::assertSame(['x' => 'y'], HeaderHelper::allToArray((object) ['x' => 'y']));
        self::assertSame(['a', 'b'], HeaderHelper::allToArray(['a', 'b']));
    }

    #[Test]
    public function allToArrayRejectsInvalidValues(): void
    {
        $this->expectException(InvalidArgumentException::class);

        HeaderHelper::allToArray(['ok', "bad\nvalue"]);
    }

    #[Test]
    public function arrayOnlyContainsStringDetectsNonStrings(): void
    {
        self::assertTrue(HeaderHelper::arrayOnlyContainsString([]));
        self::assertTrue(HeaderHelper::arrayOnlyContainsString(['a', 'b']));
        self::assertFalse(HeaderHelper::arrayOnlyContainsString(['a', 1]));
    }

    #[Test]
    public function toHeaderLinesNormalizesNamesAndMergesValues(): void
    {
        $headers = ['content-type' => 'text/html', 'x-multi-value' => ['a', 'b']];

        self::assertSame(
            ['Content-Type: text/html', 'X-Multi-Value: a,b'],
            HeaderHelper::toHeaderLines($headers),
        );
        self::assertSame(
            "Content-Type: text/html\r\nX-Multi-Value: a,b",
            HeaderHelper::toHeaderLines($headers, true),
        );
    }

    #[Test]
    public function normalizeHeaderNameCapitalizesEachSegment(): void
    {
        self::assertSame('Content-Type', HeaderHelper::normalizeHeaderName('content-type'));
        self::assertSame('X-Forwarded-For', HeaderHelper::normalizeHeaderName('x-forwarded-for'));
    }

    #[Test]
    public function prepareAttachmentHeadersSetsDownloadHeaders(): void
    {
        $response = HeaderHelper::prepareAttachmentHeaders(new Response());

        self::assertSame('application/octet-stream', $response->getHeaderLine('Content-Type'));
        self::assertSame('no-store, no-cache, must-revalidate', $response->getHeaderLine('Cache-Control'));
        self::assertSame('binary', $response->getHeaderLine('Content-Transfer-Encoding'));
        self::assertSame('none', $response->getHeaderLine('Content-Encoding'));
        self::assertFalse($response->hasHeader('Content-Disposition'));
    }

    #[Test]
    public function prepareAttachmentHeadersAddsDispositionWhenFilenameGiven(): void
    {
        $response = HeaderHelper::prepareAttachmentHeaders(new Response(), 'report.pdf');

        self::assertSame(
            "attachment; filename*=utf-8''report.pdf",
            $response->getHeaderLine('Content-Disposition'),
        );
    }

    #[Test]
    public function buildsContentDispositionForUtf8AndLegacyFilenames(): void
    {
        self::assertSame(
            "inline; filename*=utf-8''%E6%B8%AC%E8%A9%A6.txt",
            HeaderHelper::inlineContentDisposition('測試.txt'),
        );
        self::assertSame(
            'attachment; filename="a%20b.txt"',
            HeaderHelper::attachmentContentDisposition('a b.txt', false),
        );
    }

    #[Test]
    public function contentDispositionStripsUnsafeCharactersAndDotRuns(): void
    {
        self::assertSame(
            "attachment; filename*=utf-8''abc.txt",
            HeaderHelper::contentDisposition('attachment', 'a"b/c.txt'),
        );
        self::assertSame(
            "attachment; filename*=utf-8''abctxt",
            HeaderHelper::contentDisposition('attachment', 'a"b/c..txt'),
        );
    }
}
