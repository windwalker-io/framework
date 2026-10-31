<?php

declare(strict_types=1);

namespace Windwalker\Uri\Test;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\UriInterface;
use PHPUnit\Framework\TestCase;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Uri\Uri;

/**
 * Test class of Uri
 *
 * @since 2.1
 */
class UriTest extends TestCase
{
    use BaseAssertionTrait;

    protected ?Uri $instance;

    #[Before]
    protected function createInstance(): void
    {
        $this->instance = new Uri('http://someuser:somepass@www.example.com:80/path/file.html?var=value#fragment');
    }

    #[Test]
    public function constructParsesAllUriParts()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');

        self::assertEquals('https', $uri->getScheme());
        self::assertEquals('user:pass', $uri->getUserInfo());
        self::assertEquals('local.example.com', $uri->getHost());
        self::assertEquals(3001, $uri->getPort());
        self::assertEquals('user:pass@local.example.com:3001', $uri->getAuthority());
        self::assertEquals('/foo', $uri->getPath());
        self::assertEquals('bar=baz', $uri->getQuery());
        self::assertEquals('quz', $uri->getFragment());
    }

    #[Test]
    public function withSchemeReturnsNewInstanceWithScheme()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withScheme('http');
        self::assertNotSame($uri, $new);
        self::assertEquals('http', $new->getScheme());
        self::assertEquals('http://user:pass@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    #[Test]
    public function withSchemeStripsSchemeSeparator(): void
    {
        $uri = new Uri('https://local.example.com');
        $new = $uri->withScheme('https://');
        self::assertEquals('https', $new->getScheme());
    }

    #[Test]
    public function withUserInfoSetsUser()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withUserInfo('flower');
        self::assertNotSame($uri, $new);
        self::assertEquals('flower', $new->getUserInfo());
        self::assertEquals('https://flower@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    #[Test]
    public function withUserInfoSetsUserAndPassword(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withUserInfo('flower', 'sakura');
        self::assertNotSame($uri, $new);
        self::assertEquals('flower:sakura', $new->getUserInfo());
        self::assertEquals('https://flower:sakura@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    #[Test]
    public function withHostReturnsNewInstanceWithHost()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withHost('windwalker.io');
        self::assertNotSame($uri, $new);
        self::assertEquals('windwalker.io', $new->getHost());
        self::assertEquals('https://user:pass@windwalker.io:3001/foo?bar=baz#quz', (string) $new);
    }

    #[Test]
    public function withPortReturnsNewInstanceWithPort()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withPort(3000);
        self::assertNotSame($uri, $new);
        self::assertEquals(3000, $new->getPort());
        self::assertEquals(
            sprintf('https://user:pass@local.example.com:%d/foo?bar=baz#quz', 3000),
            (string) $new
        );
    }

    #[Test]
    public function withPathReplacesPath()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withPath('/bar/baz');
        self::assertNotSame($uri, $new);
        self::assertEquals('/bar/baz', $new->getPath());
        self::assertEquals('https://user:pass@local.example.com:3001/bar/baz?bar=baz#quz', (string) $new);
    }

    #[Test]
    public function withPathKeepsRelativePath(): void
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withPath('foo/bar');
        self::assertEquals('foo/bar', $new->getPath());
    }

    #[Test]
    public function stringifyingRelativePathAddsLeadingSlash(): void
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withPath('foo/bar');
        self::assertEquals('http://example.com/foo/bar', $new->__toString());
    }

    #[Test]
    public function withPathEncodesSpecialCharacters(): void
    {
        $uri = new Uri('http://example.com');
        $uri = $uri->withPath('/foo^bar');
        $expected = '/foo%5Ebar';
        self::assertEquals($expected, $uri->getPath());
    }

    #[Test]
    public function withPathDoesNotDoubleEncodePath(): void
    {
        $uri = new Uri('http://example.com');
        $uri = $uri->withPath('/foo%5Ebar');
        $expected = '/foo%5Ebar';
        self::assertEquals($expected, $uri->getPath());
    }

    #[Test]
    public function withQueryReplacesQuery()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withQuery('baz=bat');
        self::assertNotSame($uri, $new);
        self::assertEquals('baz=bat', $new->getQuery());
        self::assertEquals('https://user:pass@local.example.com:3001/foo?baz=bat#quz', (string) $new);
    }

    #[Test]
    public function withQueryStripsQuestionMark(): void
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withQuery('?foo=bar');
        self::assertEquals('foo=bar', $new->getQuery());
    }

    #[Test]
    public function withFragmentReplacesFragment()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withFragment('qat');
        self::assertNotSame($uri, $new);
        self::assertEquals('qat', $new->getFragment());
        self::assertEquals('https://user:pass@local.example.com:3001/foo?bar=baz#qat', (string) $new);
    }

    #[Test]
    public function withFragmentStripsHashSymbol(): void
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withFragment('#/foo/bar');
        self::assertEquals('/foo/bar', $new->getFragment());
    }

    public static function authorityProvider(): array
    {
        return [
            'host-only' => ['http://foo.com/bar', 'foo.com'],
            'host-port' => ['http://foo.com:3000/bar', 'foo.com:3000'],
            'user-host' => ['http://me@foo.com/bar', 'me@foo.com'],
            'user-host-port' => ['http://me@foo.com:3000/bar', 'me@foo.com:3000'],
        ];
    }

    #[DataProvider('authorityProvider')]
    #[Test]
    public function getAuthorityBuildsAuthorityFromUrl($url, $expected)
    {
        $uri = new Uri($url);
        self::assertEquals($expected, $uri->getAuthority());
    }

    public static function queryStringsForEncodingProvider(): array
    {
        return [
            'key-only' => ['k^ey', 'k%5Eey'],
            'key-value' => ['k^ey=valu`', 'k%5Eey=valu%60'],
            'array-key-only' => ['key[]', 'key%5B%5D'],
            'array-key-value' => ['key[]=valu`', 'key%5B%5D=valu%60'],
            'complex' => ['k^ey&key[]=valu`&f<>=`bar', 'k%5Eey&key%5B%5D=valu%60&f%3C%3E=%60bar'],
        ];
    }

    /**
     * @param  string  $query
     * @param  string  $expected
     */
    #[DataProvider('queryStringsForEncodingProvider')]
    #[Test]
    public function withQueryEncodesWithoutDoubleEncoding($query, $expected)
    {
        $uri = new Uri();
        $uri = $uri->withQuery($query);
        self::assertEquals($expected, $uri->getQuery());

        // No double encoded
        $uri = $uri->withQuery($expected);
        self::assertEquals($expected, $uri->getQuery());
    }

    #[Test]
    public function withFragmentEncodesWithoutDoubleEncoding()
    {
        $uri = new Uri();
        $uri = $uri->withFragment('/p^th?key^=`bar#b@z');
        $expected = '/p%5Eth?key%5E=%60bar%23b@z';
        self::assertEquals($expected, $uri->getFragment());

        // No double encoded
        $expected = '/p%5Eth?key%5E=%60bar%23b@z';
        $uri = $uri->withFragment($expected);
        self::assertEquals($expected, $uri->getFragment());
    }

    #[Test]
    public function pathConcatAppendsSegmentsWithSingleSlash(): void
    {
        $uri = new Uri('http://foo.com');
        $uri = $uri->pathConcat('hello');

        self::assertEquals('http://foo.com/hello', (string) $uri);

        $uri = $uri->withFragment('goo');
        $uri = $uri->pathConcat('/world');

        self::assertEquals('http://foo.com/hello/world#goo', (string) $uri);
    }

    public static function invalidArgumentsProvider(): array
    {
        $methods = [
            'withScheme',
            'withUserInfo',
            'withHost',
            'withPath',
            'withQuery',
            'withFragment',
        ];

        $values = [
            'null' => null,
            'true' => true,
            'false' => false,
            'zero' => 0,
            'int' => 1,
            'zero-float' => 0.0,
            'float' => 1.1,
            'array' => ['value'],
            'object' => (object) ['value' => 'value'],
        ];

        $combinations = [];

        foreach ($methods as $method) {
            foreach ($values as $type => $value) {
                $key = sprintf('%s-%s', $method, $type);

                $combinations[$key] = [$method, $value];
            }
        }

        return $combinations;
    }

    #[DataProvider('invalidArgumentsProvider')]
    #[Test]
    public function withMethodsRejectNonStringArguments($method, $value)
    {
        self::assertExpectedException(
            function () use ($method, $value) {
                $uri = new Uri('https://example.com/');
                $uri->$method($value);
            },
            InvalidArgumentException::class
        );
    }

    #[Test]
    public function toStringBuildsFullUriAfterModifications()
    {
        self::assertThat(
            $this->instance->toString(),
            self::equalTo('http://someuser:somepass@www.example.com:80/path/file.html?var=value#fragment')
        );

        $this->instance = $this->instance->withQuery('somevar=somevalue')
            ->withVar('somevar2', 'somevalue2')
            ->withScheme('ftp')
            ->withUser('root')
            ->withPassword('secret')
            ->withHost('www.example.org')
            ->withPort('8888')
            ->withFragment('someFragment')
            ->withPath('/this/is/a/path/to/a/file');

        self::assertThat(
            $this->instance->toString(),
            self::equalTo(
                // phpcs:disable
                'ftp://root:secret@www.example.org:8888/this/is/a/path/to/a/file?somevar=somevalue&somevar2=somevalue2#someFragment'
                // phpcs:enable
            )
        );
    }

    #[Test]
    public function toStringRendersSelectedParts()
    {
        self::assertEquals(
            'http://www.example.com',
            $this->instance->toString(Uri::SCHEME | Uri::HOST)
        );
        self::assertEquals(
            'someuser:somepass@www.example.com/path/file.html?var=value',
            $this->instance->toString(
                Uri::HOST
                | Uri::PATH
                | Uri::USER_INFO
                | Uri::QUERY
            )
        );
        self::assertEquals(
            'http://someuser:somepass@www.example.com:80',
            $this->instance->toString(
                Uri::FULL_HOST
            )
        );
        self::assertEquals(
            '/path/file.html?var=value#fragment',
            $this->instance->toString(
                Uri::URI | Uri::FRAGMENT
            )
        );
    }

    #[Test]
    public function withVarSetsQueryVariable()
    {
        $this->instance = $this->instance->withVar('somevariable', 'somevalue');

        self::assertThat(
            $this->instance->getVar('somevariable'),
            self::equalTo('somevalue')
        );
    }

    #[Test]
    public function hasVarChecksQueryVariable()
    {
        self::assertThat(
            $this->instance->hasVar('somevariable'),
            self::equalTo(false)
        );

        self::assertThat(
            $this->instance->hasVar('var'),
            self::equalTo(true)
        );
    }

    #[Test]
    public function getVarReturnsValueOrDefault()
    {
        self::assertThat(
            $this->instance->getVar('var'),
            self::equalTo('value')
        );

        self::assertThat(
            $this->instance->getVar('var2'),
            self::equalTo('')
        );

        self::assertThat(
            $this->instance->getVar('var2', 'default'),
            self::equalTo('default')
        );
    }

    #[Test]
    public function withoutVarRemovesQueryVariable()
    {
        self::assertThat(
            $this->instance->getVar('var'),
            self::equalTo('value')
        );

        $this->instance = $this->instance->withoutVar('var');

        self::assertThat(
            $this->instance->getVar('var'),
            self::equalTo('')
        );
    }

    #[Test]
    public function getQueryReturnsStringAndValues()
    {
        self::assertThat(
            $this->instance->getQuery(),
            self::equalTo('var=value')
        );

        self::assertThat(
            $this->instance->getQueryValues(),
            self::equalTo(['var' => 'value'])
        );
    }

    #[Test]
    public function getSchemeReturnsScheme()
    {
        self::assertThat(
            $this->instance->getScheme(),
            self::equalTo('http')
        );
    }

    #[Test]
    public function getUserReturnsUser()
    {
        self::assertThat(
            $this->instance->getUser(),
            self::equalTo('someuser')
        );
    }

    #[Test]
    public function withUserReplacesUser()
    {
        $this->instance = $this->instance->withUser('root');

        self::assertThat(
            $this->instance->getUser(),
            self::equalTo('root')
        );
    }

    #[Test]
    public function getPasswordReturnsPassword()
    {
        self::assertThat(
            $this->instance->getPassword(),
            self::equalTo('somepass')
        );
    }

    #[Test]
    public function withPasswordReplacesPassword()
    {
        $this->instance = $this->instance->withPassword('secret');

        self::assertThat(
            $this->instance->getPassword(),
            self::equalTo('secret')
        );
    }

    #[Test]
    public function getHostReturnsHost()
    {
        self::assertThat(
            $this->instance->getHost(),
            self::equalTo('www.example.com')
        );
    }

    #[Test]
    public function getPortReturnsPort()
    {
        self::assertThat(
            $this->instance->getPort(),
            self::equalTo('80')
        );
    }

    #[Test]
    public function getPathReturnsPath()
    {
        self::assertThat(
            $this->instance->getPath(),
            self::equalTo('/path/file.html')
        );
    }

    #[Test]
    public function getFragmentReturnsFragment()
    {
        self::assertThat(
            $this->instance->getFragment(),
            self::equalTo('fragment')
        );
    }

    #[Test]
    public function isSSLReturnsTrueForHttpsScheme(): void
    {
        $object = new Uri('https://someuser:somepass@www.example.com:80/path/file.html?var=value#fragment');

        self::assertThat(
            $object->isSSL(),
            self::equalTo(true)
        );
    }

    #[Test]
    public function isSSLReturnsFalseForHttpScheme(): void
    {
        $object = new Uri('http://someuser:somepass@www.example.com:80/path/file.html?var=value#fragment');

        self::assertThat(
            $object->isSSL(),
            self::equalTo(false)
        );
    }

    #[Test]
    public function wrapConvertsStringableAndStringValuesToUri(): void
    {
        $uri = new Uri('http://example.com/foo');

        self::assertSame($uri, Uri::wrap($uri));
        self::assertSame('http://example.com/bar', (string) Uri::wrap('http://example.com/bar'));
        self::assertSame('', (string) Uri::wrap(null));

        $stringable = new class () {
            public function __toString(): string
            {
                return 'http://example.com/stringable';
            }
        };

        self::assertSame('http://example.com/stringable', (string) Uri::wrap($stringable));
    }

    #[Test]
    public function wrapConvertsForeignPsrUriToInstance(): void
    {
        $psr = $this->createStub(UriInterface::class);
        $psr->method('__toString')->willReturn('http://example.com/psr');

        $uri = Uri::wrap($psr);

        self::assertInstanceOf(Uri::class, $uri);
        self::assertSame('http://example.com/psr', (string) $uri);
    }

    #[Test]
    public function constructUtf8ParsesMultibytePath(): void
    {
        $uri = new Uri('http://example.com/路徑', true);

        self::assertSame('/%E8%B7%AF%E5%BE%91', $uri->getPath());
    }

    #[Test]
    public function constructReplacesAmpEntityInQuery(): void
    {
        $uri = new Uri('http://example.com/?a=1&amp;b=2');

        self::assertSame(['a' => '1', 'b' => '2'], $uri->getQueryValues());
    }

    #[Test]
    public function getAuthorityIsEmptyWithoutHost(): void
    {
        self::assertSame('', (new Uri('/foo'))->getAuthority());
    }

    #[Test]
    public function getAuthorityOmitsStandardPortOfScheme(): void
    {
        self::assertSame('example.com', (new Uri('http://example.com:80'))->getAuthority());
        self::assertSame('example.com', (new Uri('https://example.com:443'))->getAuthority());
        self::assertSame('example.com:8080', (new Uri('http://example.com:8080'))->getAuthority());
        self::assertSame('example.com:80', (new Uri('https://example.com:80'))->getAuthority());
    }

    #[Test]
    public function withUserInfoWithEmptyUserClearsPassword(): void
    {
        $uri = new Uri('http://user:pass@example.com');
        $new = $uri->withUserInfo('', 'ignored');

        self::assertSame('', $new->getUserInfo());
        self::assertNull($new->getUser());
        self::assertNull($new->getPassword());
        self::assertSame('http://example.com/', (string) $new);
    }

    #[Test]
    public function withPortNullRemovesPort(): void
    {
        $uri = new Uri('http://example.com:8080/foo');

        self::assertNull($uri->withPort(null)->getPort());
        self::assertSame('http://example.com/foo', (string) $uri->withPort(null));
    }

    #[DataProvider('invalidPortProvider')]
    #[Test]
    public function withPortRejectsInvalidPorts(mixed $port): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Uri('http://example.com'))->withPort($port);
    }

    public static function invalidPortProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => [-1],
            'too large' => [65536],
            'array' => [[80]],
            'object' => [new \stdClass()],
        ];
    }

    #[DataProvider('invalidPathProvider')]
    #[Test]
    public function withPathRejectsQueryAndFragmentSymbols(string $path): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Uri('http://example.com'))->withPath($path);
    }

    public static function invalidPathProvider(): array
    {
        return [
            'question mark' => ['/foo?bar=1'],
            'hash' => ['/foo#bar'],
        ];
    }

    #[Test]
    public function withPathResolvesDotSegments(): void
    {
        $uri = (new Uri('http://example.com'))->withPath('/foo/bar/../baz');

        self::assertSame('/foo/baz', $uri->getPath());
    }

    #[Test]
    public function withQueryParamsAcceptsArrayAndString(): void
    {
        $uri = new Uri('http://example.com');

        self::assertSame('a=1&b%5B0%5D=2', $uri->withQueryParams(['a' => 1, 'b' => [2]])->getQuery());
        self::assertSame('x=1', $uri->withQueryParams('x=1')->getQuery());
    }

    #[Test]
    public function withPathAppendAppendsSuffixToExistingPath(): void
    {
        $uri = (new Uri('http://example.com/foo'))->withPathAppend('/bar');

        self::assertSame('http://example.com/foo/bar', (string) $uri);
    }

    #[Test]
    public function withVarRebuildsQueryString(): void
    {
        $uri = $this->instance->withVar('a', 'b');

        self::assertSame('var=value&a=b', $uri->getQuery());
        self::assertSame('var=value', $this->instance->getQuery());
    }

    #[Test]
    public function toStringOmitsPortWhenNotSetAndKeepsRelativePath(): void
    {
        self::assertSame('foo/bar?a=1#f', (new Uri('foo/bar?a=1#f'))->toString());
        self::assertSame('http://example.com/foo', (new Uri('http://example.com/foo'))->toString());
    }

    #[Test]
    public function getUserAndPasswordReturnNullWhenMissing(): void
    {
        $uri = new Uri('http://example.com');

        self::assertNull($uri->getUser());
        self::assertNull($uri->getPassword());
    }

    #[Test]
    public function getOriginalReturnsConstructedString(): void
    {
        self::assertSame('http://example.com/a?b=1', (new Uri('http://example.com/a?b=1'))->getOriginal());
    }

    #[Test]
    public function toRfc3986ConvertsToNativeUriObject(): void
    {
        if (!class_exists(\Uri\Rfc3986\Uri::class)) {
            $this->markTestSkipped('Native URI extension (PHP 8.5+) is required.');
        }

        $rfc = $this->instance->toRfc3986();

        self::assertSame('www.example.com', $rfc->getHost());
        self::assertSame('/path/file.html', $rfc->getPath());
    }

    #[Test]
    public function toWhatWgConvertsToNativeUrlObject(): void
    {
        if (!class_exists(\Uri\WhatWg\Url::class)) {
            $this->markTestSkipped('Native URI extension (PHP 8.5+) is required.');
        }

        $url = $this->instance->toWhatWg();

        self::assertSame('www.example.com', $url->getAsciiHost());
        self::assertSame('/path/file.html', $url->getPath());
    }

    #[Test]
    public function getAuthorityKeepsPortWhenSchemeIsMissing(): void
    {
        self::assertSame('example.com:8080', (new Uri('//example.com:8080/foo'))->getAuthority());
    }
}
