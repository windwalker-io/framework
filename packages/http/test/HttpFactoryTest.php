<?php

declare(strict_types=1);

namespace Windwalker\Http\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Windwalker\Http\Factory\RequestFactory;
use Windwalker\Http\Factory\ResponseFactory;
use Windwalker\Http\Factory\ServerRequestFactory;
use Windwalker\Http\Factory\StreamFactory;
use Windwalker\Http\Factory\UploadedFileFactory;
use Windwalker\Http\HttpFactory;
use Windwalker\Http\Request\Request;
use Windwalker\Uri\Uri;

class HttpFactoryTest extends TestCase
{
    #[Test]
    public function createReturnsFactoryWithDefaultDelegates(): void
    {
        $factory = HttpFactory::create();

        self::assertInstanceOf(HttpFactory::class, $factory);
        self::assertInstanceOf(RequestFactory::class, $this->delegate($factory, 'requestFactory'));
        self::assertInstanceOf(ServerRequestFactory::class, $this->delegate($factory, 'serverRequestFactory'));
        self::assertInstanceOf(ResponseFactory::class, $this->delegate($factory, 'responseFactory'));
        self::assertInstanceOf(StreamFactory::class, $this->delegate($factory, 'streamFactory'));
        self::assertInstanceOf(UploadedFileFactory::class, $this->delegate($factory, 'uploadedFileFactory'));
    }

    #[Test]
    public function constructorUsesGivenRequestFactory(): void
    {
        $delegate = $this->createMock(RequestFactoryInterface::class);
        $delegate->expects($this->once())
            ->method('createRequest')
            ->with('POST', 'http://example.com/foo')
            ->willReturn(new Request());

        $factory = new HttpFactory(requestFactory: $delegate);

        self::assertSame($delegate, $this->delegate($factory, 'requestFactory'));
        self::assertInstanceOf(RequestInterface::class, $factory->createRequest('POST', 'http://example.com/foo'));
    }

    #[Test]
    public function createRequestPassesMethodAndUriInCorrectOrder(): void
    {
        $request = new HttpFactory()->createRequest('PUT', 'http://example.com/foo?a=1');

        self::assertSame('PUT', $request->getMethod());
        self::assertSame('http://example.com/foo?a=1', (string) $request->getUri());
    }

    #[Test]
    public function createRequestAcceptsUriObject(): void
    {
        $request = new HttpFactory()->createRequest('DELETE', new Uri('http://example.com/bar'));

        self::assertSame('DELETE', $request->getMethod());
        self::assertSame('http://example.com/bar', (string) $request->getUri());
    }

    private function delegate(HttpFactory $factory, string $property): object
    {
        return new \ReflectionProperty($factory, $property)->getValue($factory);
    }
}
