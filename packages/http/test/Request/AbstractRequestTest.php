<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Request;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Test\Stub\StubRequest;
use Windwalker\Stream\Stream;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Uri\Uri;

/**
 * Test class of AbstractRequest
 *
 * @since 2.1
 */
class AbstractRequestTest extends TestCase
{
    use BaseAssertionTrait;

    protected ?StubRequest $instance;

    #[Before]
    protected function createRequest(): void
    {
        $this->instance = new StubRequest();
    }

    #[Test]
    public function constructWithoutParamsUsesEmptyUriMethodAndMemoryBody(): void
    {
        $request = new StubRequest();

        self::assertInstanceOf(Uri::class, $request->getUri());
        self::assertEquals('', (string) $request->getUri());
        self::assertEquals('', $request->getMethod());
        self::assertInstanceOf(Stream::class, $request->getBody());
        self::assertEquals('php://memory', $request->getBody()->getMetadata('uri'));
        self::assertEquals([], $request->getHeaders());
    }

    #[Test]
    public function constructWithScalarParamsNormalizesUriMethodBodyAndHeaders(): void
    {
        $uri = 'http://example.com/?foo=bar#baz';
        $method = 'post';
        $body = fopen($tmpfile = tempnam(sys_get_temp_dir(), 'windwalker'), 'wb+');
        $headers = [
            'X-Foo' => ['Flower', 'Sakura'],
            'Content-Type' => 'application/json',
        ];

        $request = new StubRequest($uri, $method, $body, $headers);

        self::assertInstanceOf(Uri::class, $request->getUri());
        self::assertEquals('http://example.com/?foo=bar#baz', (string) $request->getUri());
        self::assertEquals('POST', $request->getMethod());
        self::assertInstanceOf(Stream::class, $request->getBody());
        self::assertEquals($tmpfile, $request->getBody()->getMetadata('uri'));
        self::assertEquals(['Flower', 'Sakura'], $request->getHeader('x-foo'));
        self::assertEquals(['application/json'], $request->getHeader('content-type'));

        fclose($body);
    }

    #[Test]
    public function constructWithObjectParamsKeepsSameUriAndBodyInstances(): void
    {
        $uri = new Uri('http://example.com/flower/sakura?foo=bar#baz');
        $body = new Stream();
        $request = new StubRequest($uri, null, $body);

        self::assertSame($uri, $request->getUri());
        self::assertSame($body, $request->getBody());
    }

    #[Test]
    public function withRequestTargetOverridesTargetDerivedFromUri(): void
    {
        self::assertEquals('/', $this->instance->getRequestTarget());

        $request = $this->instance->withUri(new Uri('http://example.com/flower/sakura?foo=bar#baz'));

        self::assertNotSame($request, $this->instance);
        self::assertEquals('/flower/sakura?foo=bar', (string) $request->getRequestTarget());

        $request = $request->withUri(new Uri('http://example.com'));

        self::assertEquals('/', (string) $request->getRequestTarget());

        $request = $request->withRequestTarget('*');

        self::assertEquals('*', $request->getRequestTarget());
    }

    #[Test]
    public function withMethodReturnsNewInstanceWithUppercasedMethod(): void
    {
        self::assertEquals('', $this->instance->getMethod());

        $request = $this->instance->withMethod('patch');

        self::assertNotSame($request, $this->instance);
        self::assertEquals('PATCH', $request->getMethod());
    }

    #[Test]
    public function withMethodThrowsOnInvalidMethod(): void
    {
        $request = $this->instance->withMethod('patch');

        self::assertExpectedException(
            function () use ($request) {
                $request->withMethod('FLY');
            },
            new InvalidArgumentException()
        );
    }

    #[Test]
    public function withUriPreservingHostKeepsHostHeaderUntouched(): void
    {
        self::assertInstanceOf(Uri::class, $this->instance->getUri());
        self::assertEquals('', (string) $this->instance->getUri());

        $request = $this->instance->withUri(new Uri('http://example.com/flower/sakura?foo=bar#baz'), true);

        self::assertNotSame($request, $this->instance);
        self::assertEquals('http://example.com/flower/sakura?foo=bar#baz', (string) $request->getUri());
        self::assertEquals([], $request->getHeader('host'));
    }

    #[Test]
    public function withUriUpdatesHostHeaderFromUri(): void
    {
        $request = $this->instance->withUri(new Uri('http://windwalker.io/flower/sakura?foo=bar#baz'));

        self::assertEquals('http://windwalker.io/flower/sakura?foo=bar#baz', (string) $request->getUri());
        self::assertEquals(['windwalker.io'], $request->getHeader('host'));
    }

    #[Test]
    public function getRequestTargetDerivesFromUriPathAndQuery(): void
    {
        self::assertSame('/', $this->instance->getRequestTarget());

        $request = $this->instance->withUri(new Uri('http://example.com/foo/bar?a=1'));
        self::assertSame('/foo/bar?a=1', $request->getRequestTarget());

        $request = $this->instance->withUri(new Uri('http://example.com'));
        self::assertSame('/', $request->getRequestTarget());
    }

    #[Test]
    public function withRequestTargetThrowsOnWhitespace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('whitespace');

        $this->instance->withRequestTarget('/foo bar');
    }

    #[Test]
    public function constructThrowsOnInvalidHeaders(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new StubRequest(null, null, 'php://memory', ['X-Foo' => [1]]);
    }

    #[Test]
    public function constructThrowsOnInvalidHeaderName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Invalid header name');

        new StubRequest(null, null, 'php://memory', ['Bad Name' => 'foo']);
    }

    #[Test]
    public function withUriAddsPortToHostHeader(): void
    {
        $request = $this->instance->withUri(new Uri('http://example.com:8080/foo'));

        self::assertSame(['example.com:8080'], $request->getHeader('host'));
    }

    #[Test]
    public function withUriKeepsHostHeaderWhenUriHasNoHost(): void
    {
        $request = $this->instance->withHeader('Host', 'keep.me')->withUri(new Uri('/relative/path'));

        self::assertSame(['keep.me'], $request->getHeader('host'));
    }

    #[Test]
    public function getMethodReturnsEmptyStringWhenNotSet(): void
    {
        self::assertSame('', $this->instance->getMethod());
    }
}
