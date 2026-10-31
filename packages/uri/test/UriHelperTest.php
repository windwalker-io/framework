<?php

declare(strict_types=1);

namespace Windwalker\Uri\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Windwalker\Uri\UriHelper;

/**
 * Tests for the Windwalker\Uri\UriHelper class.
 *
 * @since  2.0
 */
class UriHelperTest extends TestCase
{
    #[Test]
    public function parseUrlParsesQueryString(): void
    {
        // phpcs:disable
        $url = 'http://localhost/Windwalker_development/j16_trunk/administrator/index.php?option=com_contact&view=contact&layout=edit&id=5';
        // phpcs:enable
        $expected = parse_url($url);
        $actual = UriHelper::parseUrl($url);
        self::assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');
    }

    #[Test]
    public function parseUrlParsesFragmentContainingQueryLikeString(): void
    {
        // Test all parts of query
        $url = 'https://john:doe@www.google.com:80/folder/page.html#id?var=kay&var2=key&true';
        $expected = parse_url($url);
        $actual = UriHelper::parseUrl($url);
        self::assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');
    }

    #[Test]
    public function parseUrlKeepsUtf8CharactersInPath(): void
    {
        // Test special characters in URL
        $url = 'http://Windwalker.org/mytestpath/È';
        $expected = parse_url($url);

        // Fix up path for UTF-8 characters
        $expected['path'] = '/mytestpath/È';
        $actual = UriHelper::parseUrl($url);
        self::assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');
    }

    #[Test]
    public function parseUrlKeepsSpecialCharactersInUrl(): void
    {
        // Test special characters in URL
        $url = 'http://mydomain.com/!*\'();:@&=+$,/?%#[]" \\';
        $expected = parse_url($url);
        $actual = UriHelper::parseUrl($url);
        self::assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');
    }

    #[Test]
    public function parseUrlKeepsPercentEncodedPath(): void
    {
        // Test url encoding in URL
        $url = 'http://mydomain.com/%21%2A%27%28%29%3B%3A%40%26%3D%24%2C%2F%3F%25%23%5B%22%20%5C';
        $expected = parse_url($url);
        $actual = UriHelper::parseUrl($url);
        self::assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');
    }

    #[Test]
    public function parseUrlHandlesMixedUtf8AndPercentCharacters(): void
    {
        // Test a mix of the above
        $url = 'http://john:doe@mydomain.com:80/%È21%25È3*%(';
        $expected = parse_url($url);

        // Fix up path for UTF-8 characters
        $expected['path'] = '/%È21%25È3*%(';
        $actual = UriHelper::parseUrl($url);
        self::assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');
    }

    #[Test]
    public function parseUrlHandlesInvalidUrlLikeNativeParseUrl(): void
    {
        // Test invalild URL
        $url = 'http:///mydomain.com';
        $expected = parse_url($url);
        $actual = UriHelper::parseUrl($url);
        self::assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');
    }

    #[DataProvider('filterSchemeProvider')]
    #[Test]
    public function filterSchemeLowercasesAndRemovesSeparator(string $scheme, string $expected): void
    {
        self::assertSame($expected, UriHelper::filterScheme($scheme));
    }

    public static function filterSchemeProvider(): array
    {
        return [
            'upper case' => ['HTTP', 'http'],
            'colon' => ['https:', 'https'],
            'colon and slashes' => ['ftp://', 'ftp'],
            'empty' => ['', ''],
        ];
    }

    #[Test]
    public function filterQueryStripsQuestionMarkAndEncodesKeysAndValues(): void
    {
        self::assertSame(
            'k%5Eey=valu%60&flag&a%5B%5D=b%20c',
            UriHelper::filterQuery('?k^ey=valu`&flag&a[]=b c')
        );
    }

    #[Test]
    public function filterQueryDoesNotDoubleEncode(): void
    {
        self::assertSame('a=%5E&b=%60', UriHelper::filterQuery('a=%5E&b=%60'));
    }

    #[Test]
    public function splitQueryValueReturnsNullValueWhenNoEqualSign(): void
    {
        self::assertSame(['foo', null], UriHelper::splitQueryValue('foo'));
        self::assertSame(['foo', 'bar=baz'], UriHelper::splitQueryValue('foo=bar=baz'));
    }

    #[Test]
    public function filterFragmentStripsHashAndEncodes(): void
    {
        self::assertSame('/foo%5Ebar', UriHelper::filterFragment('#/foo^bar'));
        self::assertSame('', UriHelper::filterFragment(''));
    }

    #[Test]
    public function filterPathEncodesUnsafeCharactersWithoutDoubleEncoding(): void
    {
        self::assertSame('/foo%5Ebar%20baz/%5E', UriHelper::filterPath('/foo^bar baz/%5E'));
        self::assertSame('/100%25', UriHelper::filterPath('/100%'));
    }

    #[Test]
    public function encodeAndDecodeSupportStringsAndNestedArrays(): void
    {
        self::assertSame('a+b%2Fc', UriHelper::encode('a b/c'));
        self::assertSame('a%20b%2Fc', UriHelper::encode('a b/c', true));
        self::assertSame('a b/c', UriHelper::decode('a+b%2Fc'));
        self::assertSame('a+b/c', UriHelper::decode('a+b%2Fc', true));
        self::assertSame(['a%20b', ['c%20d']], UriHelper::encode(['a b', ['c d']], true));
        self::assertSame(['a b', ['c d']], UriHelper::decode(['a%20b', ['c%20d']], true));
    }

    #[DataProvider('isAbsoluteProvider')]
    #[Test]
    public function isAbsoluteDetectsAbsolutePaths(string $path, bool $expected): void
    {
        self::assertSame($expected, UriHelper::isAbsolute($path));
    }

    public static function isAbsoluteProvider(): array
    {
        return [
            'empty' => ['', false],
            'url' => ['http://example.com', true],
            'unix root' => ['/foo', true],
            'backslash root' => ['\\foo', true],
            'windows drive only' => ['C:', true],
            'windows drive slash' => ['C:/foo', true],
            'windows drive backslash' => ['C:\\foo', true],
            'windows drive without separator' => ['C:foo', false],
            'relative' => ['foo/bar', false],
            'non alpha colon' => ['1:/foo', false],
        ];
    }
}
