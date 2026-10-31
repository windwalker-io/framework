<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Request;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;
use Windwalker\Http\Factory\ServerRequestFactory;
use Windwalker\Http\Request\RequestBaseUri;
use Windwalker\Http\Request\ServerRequest;
use Windwalker\Http\UploadedFile;
use Windwalker\Stream\Stream;
use Windwalker\Uri\Uri;

/**
 * Test class of ServerRequestFactory
 *
 * @since 3.0
 */
class ServerRequestFactoryTest extends TestCase
{
    protected mixed $apacheRequestHeadersBackup = null;

    #[Test]
    public function createFromGlobalsReturnsServerRequest(): void
    {
        $request = ServerRequestFactory::createFromGlobals();

        self::assertTrue($request instanceof ServerRequest);
    }

    #[Before]
    protected function backupApacheRequestHeadersResolver(): void
    {
        $this->apacheRequestHeadersBackup = ServerRequestFactory::$apacheRequestHeaders;
    }

    #[After]
    protected function restoreApacheRequestHeadersResolver(): void
    {
        ServerRequestFactory::$apacheRequestHeaders = $this->apacheRequestHeadersBackup;
    }

    #[Test]
    public function prepareServersAddsAuthorizationHeaderFromApacheRequestHeaders(): void
    {
        ServerRequestFactory::$apacheRequestHeaders = [$this, 'apacheRequestHeaders'];

        $server = [];

        $server = ServerRequestFactory::prepareServers($server);

        self::assertEquals('foo', $server['HTTP_AUTHORIZATION']);
    }

    #[Test]
    public function prepareServersSkipsAuthorizationWhenApacheHeadersAreEmpty(): void
    {
        ServerRequestFactory::$apacheRequestHeaders = [$this, 'apacheRequestHeadersEmpty'];

        $server = [];

        $server = ServerRequestFactory::prepareServers($server);

        self::assertTrue(empty($server['HTTP_AUTHORIZATION']));
    }

    public function apacheRequestHeaders(): array
    {
        return [
            'authorization' => 'foo',
        ];
    }

    public function apacheRequestHeadersEmpty(): array
    {
        return [];
    }

    #[Test]
    public function prepareFilesConvertsFlatFileSpecToUploadedFile(): void
    {
        $files = [
            [
                'tmp_name' => 'php://temp',
                'size' => 123,
                'error' => 0,
                'name' => 'foo_name',
                'type' => 'foo_type',
            ],
        ];

        $files = ServerRequestFactory::prepareFiles($files);

        self::assertInstanceOf(UploadedFile::class, $files[0]);
        self::assertInstanceOf(Stream::class, $files[0]->getStream());
        self::assertEquals('foo_name', $files[0]->getClientFilename());
        self::assertEquals(0, $files[0]->getError());
    }

    #[Test]
    public function prepareFilesConvertsNestedFileSpecsToUploadedFiles(): void
    {
        $files = [
            'first' => [
                'tmp_name' => [
                    'foo' => 'php://temp',
                    'bar' => 'php://temp',
                ],
                'size' => [
                    'foo' => 123,
                    'bar' => 321,
                ],
                'error' => [
                    'foo' => 1,
                    'bar' => 2,
                ],
                'name' => [
                    'foo' => 'foo_name',
                    'bar' => 'bar_name',
                ],
                'type' => [
                    'foo' => 'foo_type',
                    'bar' => 'bar_type',
                ],
            ],
            'second' => [
                'tmp_name' => 'php://temp',
                'size' => 456,
                'error' => 0,
                'name' => 'second_name',
                'type' => 'second_type',
            ],
        ];

        $files = ServerRequestFactory::prepareFiles($files);

        self::assertTrue($files['first']['foo'] instanceof UploadedFile);
        self::assertTrue($files['first']['bar'] instanceof UploadedFile);
        self::assertTrue($files['second'] instanceof UploadedFile);
    }

    #[Test]
    public function prepareHeadersNormalizesHttpAndContentServerKeys(): void
    {
        $headers = [
            'HTTP_X_FOO' => 'foo',
            'HTTP_X_BAR' => 'bar',
            'HTTP_X_FLOWER' => 'Sakura',
            'CONTENT_YOO' => 'baz',
            'CONTENT_BIRD' => 'fly',
        ];

        $expected = [
            'x-foo' => 'foo',
            'x-bar' => 'bar',
            'x-flower' => 'Sakura',
            'content-yoo' => 'baz',
            'content-bird' => 'fly',
        ];

        self::assertEquals($expected, ServerRequestFactory::prepareHeaders($headers)->dump());
    }

    #[DataProvider('prepareUriProvider')]
    #[Test]
    public function prepareUriBuildsUriFromServerParamsAndHeaders(
        array $servers,
        array $headers,
        string $expected,
        int $line,
    ): void {
        $uri = ServerRequestFactory::prepareUri($servers, $headers);

        self::assertEquals($expected, $uri->__toString());
    }

    public static function prepareUriProvider(): array
    {
        return [
            '#apache-normal' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar?a=wrong',
                ],
                [],
                'http://example.com:8080/foo/bar?a=b&c=d',
                __LINE__,
            ],
            '#apache-fragment' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar#test?a=b&c=d',
                ],
                [],
                'http://example.com:8080/foo/bar?a=b&c=d#test',
                __LINE__,
            ],
            '#apache-https' => [
                [
                    'HTTPS' => 'on',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar?a=wrong',
                ],
                [],
                'https://example.com:8080/foo/bar?a=b&c=d',
                __LINE__,
            ],
            '#apache-x-forwarded' => [
                [
                    'HTTPS' => '',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar?a=wrong',
                ],
                [
                    'x-forwarded-proto' => 'https',
                ],
                'https://example.com:8080/foo/bar?a=b&c=d',
                __LINE__,
            ],
            '#apache-header-host' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => '',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar?a=wrong',
                ],
                [
                    'host' => 'example.com',
                ],
                // Will never get port because host in header is a cache
                'http://example.com/foo/bar?a=b&c=d',
                __LINE__,
            ],
            '#apache-ipv6' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => '[2001:db8:a0b:12f0::1]',
                    'SERVER_ADDR' => '2001:db8:a0b:12f0::1',
                    'SERVER_PORT' => 8080,
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar?a=wrong',
                ],
                [],
                'http://[2001:db8:a0b:12f0::1]:8080/foo/bar?a=b&c=d',
                __LINE__,
            ],
            '#iis-rewritten' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => 'example.com',
                    'IIS_WasUrlRewritten' => '1',
                    'UNENCODED_URL' => 'flower/sakura',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar?a=wrong',
                ],
                [],
                'http://example.com:8080/flower/sakura?a=b&c=d',
                __LINE__,
            ],
            '#iis-x-rewrite' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar?a=wrong',
                    'HTTP_X_REWRITE_URL' => '/flower/sakura?a=wrong',
                ],
                [],
                'http://example.com:8080/flower/sakura?a=b&c=d',
                __LINE__,
            ],
            '#iis-origin-url' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'REQUEST_URI' => '/foo/bar?a=wrong',
                    'HTTP_X_REWRITE_URL' => '/flower/sakura?a=wrong',
                    'HTTP_X_ORIGINAL_URL' => '/flower/olive?a=wrong',
                ],
                [],
                'http://example.com:8080/flower/olive?a=b&c=d',
                __LINE__,
            ],
            '#orig-path-info' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                    'ORIG_PATH_INFO' => '/flower/rose',
                ],
                [],
                'http://example.com:8080/flower/rose?a=b&c=d',
                __LINE__,
            ],
            '#no-path' => [
                [
                    'HTTPS' => 'off',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '8080',
                    'QUERY_STRING' => '?a=b&c=d',
                ],
                [],
                'http://example.com:8080/?a=b&c=d',
                __LINE__,
            ],
        ];
    }

    #[Test]
    #[DataProvider('hostAndPortProvider')]
    public function getHostAndPortFromHeadersReturnsHostAndPort(
        array $server,
        array $headers,
        string $expectedHost,
        ?int $expectedPort,
    ): void {
        $host = '';
        $port = null;

        ServerRequestFactory::getHostAndPortFromHeaders($host, $port, $server, $headers);

        self::assertSame($expectedHost, $host);
        self::assertSame($expectedPort, $port);
    }

    public static function hostAndPortProvider(): array
    {
        return [
            'host header with port' => [[], ['host' => 'example.com:8080'], 'example.com', 8080],
            'host header without port' => [[], ['host' => 'example.com'], 'example.com', null],
            'ipv6 host header' => [[], ['host' => '[::1]:81'], '[::1]', 81],
            'multiple hosts uses first' => [[], ['host' => 'a.com, b.com'], 'a.com', null],
            'invalid host header ignored' => [[], ['host' => 'bad host!'], '', null],
            'server name and port' => [
                ['SERVER_NAME' => 'srv.test', 'SERVER_PORT' => '8000'],
                [],
                'srv.test',
                8000,
            ],
            'server name only' => [['SERVER_NAME' => 'srv.test'], [], 'srv.test', null],
            'nothing available' => [[], [], '', null],
            'ipv6 server name uses server addr' => [
                ['SERVER_NAME' => '[::1]', 'SERVER_ADDR' => '::1', 'SERVER_PORT' => '8080'],
                [],
                '[::1]',
                8080,
            ],
            'ipv6 server name defaults port 80' => [
                ['SERVER_NAME' => '[::1]', 'SERVER_ADDR' => '::1'],
                [],
                '[::1]',
                80,
            ],
        ];
    }

    #[Test]
    #[DataProvider('requestUriProvider')]
    public function getRequestUriReturnsRequestUriFromServerParams(array $server, string $expected): void
    {
        self::assertSame($expected, ServerRequestFactory::getRequestUri($server));
    }

    public static function requestUriProvider(): array
    {
        return [
            'request uri' => [['REQUEST_URI' => '/foo?bar=1'], '/foo?bar=1'],
            'absolute request uri' => [['REQUEST_URI' => 'http://example.com/foo'], '/foo'],
            'IIS rewrite' => [
                ['IIS_WasUrlRewritten' => '1', 'UNENCODED_URL' => '/iis//path', 'REQUEST_URI' => '/other'],
                '/iis//path',
            ],
            'IIS rewrite without unencoded url' => [
                ['IIS_WasUrlRewritten' => '1', 'REQUEST_URI' => '/other'],
                '/other',
            ],
            'x rewrite url' => [['REQUEST_URI' => '/a', 'HTTP_X_REWRITE_URL' => '/rewrite'], '/rewrite'],
            'x original url wins' => [
                ['REQUEST_URI' => '/a', 'HTTP_X_REWRITE_URL' => '/rewrite', 'HTTP_X_ORIGINAL_URL' => '/original'],
                '/original',
            ],
            'orig path info' => [['ORIG_PATH_INFO' => '/orig'], '/orig'],
            'fallback' => [[], '/'],
        ];
    }

    #[Test]
    #[DataProvider('stripQueryStringProvider')]
    public function stripQueryStringRemovesQueryString(string $path, string $expected): void
    {
        self::assertSame($expected, ServerRequestFactory::stripQueryString($path));
    }

    public static function stripQueryStringProvider(): array
    {
        return [
            'with query' => ['/foo?bar=1', '/foo'],
            'without query' => ['/foo', '/foo'],
            'only query' => ['?a=1', ''],
        ];
    }

    #[Test]
    public function createServerRequestBuildsRequestWithMethodUriAndServerParams(): void
    {
        $factory = new ServerRequestFactory();

        $request = $factory->createServerRequest('post', 'http://example.com/foo', ['FOO' => 'bar']);

        self::assertInstanceOf(ServerRequest::class, $request);
        self::assertSame('POST', $request->getMethod());
        self::assertSame('http://example.com/foo', (string) $request->getUri());
        self::assertSame(['FOO' => 'bar'], $request->getServerParams());
    }

    #[Test]
    public function createFromGlobalsBuildsRequestFromServerParams(): void
    {
        $request = ServerRequestFactory::createFromGlobals(
            [
                'REQUEST_METHOD' => 'PUT',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'example.com:8443',
                'REQUEST_URI' => '/foo/bar?a=1',
                'QUERY_STRING' => 'a=1',
                'SERVER_PROTOCOL' => 'HTTP/2.0',
                'CONTENT_TYPE' => 'text/plain',
            ],
            ['a' => '1'],
            ['p' => '2'],
            ['c' => '3'],
        );

        self::assertSame('PUT', $request->getMethod());
        self::assertSame('https://example.com:8443/foo/bar?a=1', (string) $request->getUri());
        self::assertSame('2.0', $request->getProtocolVersion());
        self::assertSame(['a' => '1'], $request->getQueryParams());
        self::assertSame(['p' => '2'], $request->getParsedBody());
        self::assertSame(['c' => '3'], $request->getCookieParams());
        self::assertSame('text/plain', $request->getHeaderLine('content-type'));
        self::assertSame('example.com:8443', $request->getHeaderLine('host'));
        self::assertSame('PUT', $request->getServerParams()['REQUEST_METHOD']);
    }

    #[Test]
    public function createFromGlobalsConvertsProvidedFiles(): void
    {
        $request = ServerRequestFactory::createFromGlobals(
            ['REQUEST_METHOD' => 'POST'],
            files: [
                'upload' => [
                    'tmp_name' => 'php://temp',
                    'size' => 1,
                    'error' => 0,
                    'name' => 'a.txt',
                    'type' => 'text/plain',
                ],
            ],
        );

        self::assertInstanceOf(UploadedFile::class, $request->getUploadedFiles()['upload']);
    }

    #[Test]
    public function createFromUriReplacesUriAndSetsScriptName(): void
    {
        $request = ServerRequestFactory::createFromUri(
            'http://example.com/index.php/foo',
            'index.php',
            ['REQUEST_METHOD' => 'GET'],
        );

        self::assertSame('http://example.com/index.php/foo', (string) $request->getUri());
        self::assertSame('/index.php', $request->getServerParams()['SCRIPT_NAME']);
        self::assertSame('example.com', $request->getHeaderLine('host'));
    }

    #[Test]
    public function createFromUriAcceptsUriObjectAndNormalizesScriptName(): void
    {
        $uri = new Uri('https://example.org/bar');

        $request = ServerRequestFactory::createFromUri($uri, null, ['SCRIPT_NAME' => 'app.php']);

        self::assertSame($uri, $request->getUri());
        self::assertSame('/app.php', $request->getServerParams()['SCRIPT_NAME']);
    }

    #[Test]
    public function createBaseUriFromRequestParsesRequest(): void
    {
        $request = ServerRequestFactory::createFromUri(
            'http://example.com/index.php/foo',
            '/index.php',
            ['REQUEST_METHOD' => 'GET', 'SCRIPT_FILENAME' => '/var/www/index.php'],
        );

        $base = ServerRequestFactory::createBaseUriFromRequest($request);

        self::assertInstanceOf(RequestBaseUri::class, $base);
    }

    #[Test]
    #[DataProvider('protocolVersionProvider')]
    public function getProtocolVersionParsesServerProtocol(array $server, string $expected): void
    {
        self::assertSame($expected, ServerRequestFactory::getProtocolVersion($server));
    }

    public static function protocolVersionProvider(): array
    {
        return [
            'default' => [[], '1.1'],
            'http 1.0' => [['SERVER_PROTOCOL' => 'HTTP/1.0'], '1.0'],
            'without prefix' => [['SERVER_PROTOCOL' => '1.1'], '1.1'],
        ];
    }

    #[Test]
    public function getProtocolVersionThrowsOnInvalidFormat(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessageIsOrContains('Invalid protocol version format');

        ServerRequestFactory::getProtocolVersion(['SERVER_PROTOCOL' => 'HTTP/2']);
    }

    #[Test]
    public function prepareFilesKeepsFullPathOfFlatAndNestedSpecs(): void
    {
        $files = ServerRequestFactory::prepareFiles([
            'flat' => [
                'tmp_name' => 'php://temp',
                'size' => 1,
                'error' => 0,
                'name' => 'a.txt',
                'type' => 'text/plain',
                'full_path' => 'dir/a.txt',
            ],
            'nested' => [
                'tmp_name' => ['x' => 'php://temp'],
                'size' => ['x' => 1],
                'error' => ['x' => 0],
                'name' => ['x' => 'b.txt'],
                'type' => ['x' => 'text/plain'],
                'full_path' => ['x' => 'dir/b.txt'],
            ],
        ]);

        self::assertSame('dir/a.txt', $files['flat']->getFullPath());
        self::assertSame('dir/b.txt', $files['nested']['x']->getFullPath());
    }

    #[Test]
    public function prepareHeadersSkipsEmptyValuesAndNonHeaderKeys(): void
    {
        $headers = ServerRequestFactory::prepareHeaders([
            'HTTP_X_EMPTY' => '',
            'SERVER_NAME' => 'example.com',
            'HTTP_X_REAL_IP' => '1.2.3.4',
        ])->dump();

        self::assertSame(['x-real-ip' => '1.2.3.4'], $headers);
    }

    #[Test]
    public function prepareServersKeepsExistingAuthorizationHeader(): void
    {
        ServerRequestFactory::$apacheRequestHeaders = fn() => ['Authorization' => 'Bearer other'];

        $server = ServerRequestFactory::prepareServers(['HTTP_AUTHORIZATION' => 'Bearer mine']);

        self::assertSame('Bearer mine', $server['HTTP_AUTHORIZATION']);
    }
}
