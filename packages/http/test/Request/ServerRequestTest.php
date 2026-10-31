<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Request;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Windwalker\Http\Request\ServerRequest;
use Windwalker\Http\SafeJson;
use Windwalker\Http\UploadedFile;
use Windwalker\Stream\PhpInputStream;
use Windwalker\Uri\Uri;
use Windwalker\Utilities\Reflection\ReflectAccessor;

class ServerRequestTest extends TestCase
{
    protected ?ServerRequest $instance;

    #[Before]
    protected function createServerRequest(): void
    {
        $this->instance = new ServerRequest();
    }

    #[Test]
    public function constructAppliesServerParamsFilesUriMethodBodyAndHeaders(): void
    {
        $server = [
            'foo' => 'bar',
            'baz' => 'bat',
        ];
        $server['server'] = true;

        $files = [
            'files' => new UploadedFile('php://temp', 0),
        ];

        $uri = new Uri('http://example.com');
        $method = 'POST';
        $headers = [
            'Host' => ['example.com'],
        ];

        $request = new ServerRequest(
            $server,
            $files,
            $uri,
            $method,
            'php://memory',
            $headers,
        );

        $this->assertEquals($server, $request->getServerParams());
        $this->assertEquals($files, $request->getUploadedFiles());
        $this->assertSame($uri, $request->getUri());
        $this->assertEquals($method, $request->getMethod());
        $this->assertEquals($headers, $request->getHeaders());

        $body = $request->getBody();
        $stream = ReflectAccessor::getValue($body, 'stream');

        $this->assertEquals('php://memory', $stream);
    }

    #[Test]
    public function constructUsesPhpInputStreamByDefault(): void
    {
        self::assertInstanceOf(PhpInputStream::class, $this->instance->getBody());
    }

    #[Test]
    public function constructAppliesCookiesQueryParsedBodyAndProtocol(): void
    {
        $request = new ServerRequest(
            cookies: ['a' => '1'],
            queryParams: ['b' => '2'],
            parsedBody: ['c' => '3'],
            protocol: '2.0',
        );

        self::assertSame(['a' => '1'], $request->getCookieParams());
        self::assertSame(['b' => '2'], $request->getQueryParams());
        self::assertSame(['c' => '3'], $request->getParsedBody());
        self::assertSame('2.0', $request->getProtocolVersion());
    }

    #[Test]
    public function constructThrowsOnInvalidUploadedFiles(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Invalid uploaded files');

        new ServerRequest([], ['file' => 'not-an-uploaded-file']);
    }

    #[Test]
    public function withCookieParamsReturnsNewInstanceWithCookieParams(): void
    {
        $new = $this->instance->withCookieParams(['sid' => 'abc']);

        self::assertNotSame($this->instance, $new);
        self::assertSame(['sid' => 'abc'], $new->getCookieParams());
        self::assertSame([], $this->instance->getCookieParams());
    }

    #[Test]
    public function withQueryParamsReturnsNewInstanceWithQueryParams(): void
    {
        $new = $this->instance->withQueryParams(['page' => '2']);

        self::assertNotSame($this->instance, $new);
        self::assertSame(['page' => '2'], $new->getQueryParams());
        self::assertSame([], $this->instance->getQueryParams());
    }

    #[Test]
    public function withUploadedFilesReturnsNewInstanceWithUploadedFiles(): void
    {
        $files = ['file' => new UploadedFile('php://temp', 0)];

        $new = $this->instance->withUploadedFiles($files);

        self::assertNotSame($this->instance, $new);
        self::assertSame($files, $new->getUploadedFiles());
        self::assertSame([], $this->instance->getUploadedFiles());
    }

    #[Test]
    public function withUploadedFilesThrowsOnInvalidFile(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->instance->withUploadedFiles(['file' => new stdClass()]);
    }

    #[Test]
    public function getParsedBodyDecodesSafeJsonLazily(): void
    {
        $request = $this->instance->withParsedBody(new SafeJson('{"foo":"bar"}', true));

        self::assertSame(['foo' => 'bar'], $request->getParsedBody());
    }

    #[Test]
    public function getParsedBodyReturnsNullByDefault(): void
    {
        self::assertNull($this->instance->getParsedBody());
    }

    #[Test]
    public function withParsedBodyReturnsNewInstanceWithParsedBody(): void
    {
        $object = new stdClass();
        $new = $this->instance->withParsedBody($object);

        self::assertNotSame($this->instance, $new);
        self::assertSame($object, $new->getParsedBody());
        self::assertNull($this->instance->getParsedBody());
    }

    #[Test]
    public function getAttributeReturnsAttributeOrDefault(): void
    {
        $request = $this->instance->withAttribute('nullable', null)->withAttribute('foo', 'bar');

        self::assertSame('bar', $request->getAttribute('foo'));
        self::assertNull($request->getAttribute('nullable', 'default'));
        self::assertSame('default', $request->getAttribute('missing', 'default'));
        self::assertNull($request->getAttribute('missing'));
    }

    #[Test]
    public function withAttributeReturnsNewInstanceWithAttribute(): void
    {
        $new = $this->instance->withAttribute('foo', 'bar');

        self::assertNotSame($this->instance, $new);
        self::assertSame(['foo' => 'bar'], $new->getAttributes());
        self::assertSame([], $this->instance->getAttributes());
    }

    #[Test]
    public function withoutAttributeReturnsNewInstanceWithoutAttribute(): void
    {
        $request = $this->instance->withAttribute('foo', 'bar')->withAttribute('baz', 'bat');

        $new = $request->withoutAttribute('foo');

        self::assertSame(['baz' => 'bat'], $new->getAttributes());
        self::assertSame(['foo' => 'bar', 'baz' => 'bat'], $request->getAttributes());
    }

    #[Test]
    public function withoutAttributeReturnsCloneWhenAttributeIsMissing(): void
    {
        $new = $this->instance->withoutAttribute('missing');

        self::assertNotSame($this->instance, $new);
        self::assertSame([], $new->getAttributes());
    }
}
