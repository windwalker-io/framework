<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Middleware;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplFileInfo;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Mime\MimeTypesInterface;
use Windwalker\Http\Factory\ServerRequestFactory;
use Windwalker\Http\Middleware\FileProcessMiddleware;
use Windwalker\Http\Response\Response;

class FileProcessMiddlewareTest extends TestCase
{
    #[Before]
    protected function setUpVirtualFilesystem(): void
    {
        vfsStream::setup('public', null, [
            'style.css' => 'body{}',
            'sub' => ['data.foo' => 'FOO'],
        ]);
    }

    private function handler(): RequestHandlerInterface
    {
        return new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return Response::fromString('next', 404);
            }
        };
    }

    private function dispatch(FileProcessMiddleware $middleware, string $path): ResponseInterface
    {
        return $middleware->process(
            ServerRequestFactory::createFromUri('http://example.com' . $path),
            $this->handler(),
        );
    }

    #[Test]
    public function processServesExistingFileWithMimeAndLastModified(): void
    {
        $res = $this->dispatch(new FileProcessMiddleware('vfs://public'), '/style.css');

        self::assertSame('body{}', (string) $res->getBody());
        self::assertSame('text/css', $res->getHeaderLine('Content-Type'));
        self::assertNotSame('', $res->getHeaderLine('Last-modified'));
    }

    #[Test]
    public function processServesNestedFilesWithCustomMimes(): void
    {
        $middleware = new FileProcessMiddleware('vfs://public', ['application/x-foo' => ['foo']]);

        $res = $this->dispatch($middleware, '/sub/data.foo');

        self::assertSame('FOO', (string) $res->getBody());
        self::assertSame('application/x-foo', $res->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function processFallsBackToTextPlainForUnknownExtension(): void
    {
        $middleware = new FileProcessMiddleware('vfs://public');

        $res = $this->dispatch($middleware, '/sub/data.foo');

        self::assertSame('text/plain', $res->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function processAppliesStaticAndCallableHeaderOptions(): void
    {
        $middleware = new FileProcessMiddleware(
            'vfs://public',
            options: [
                'headers' => [
                    'Cache-Control' => 'max-age=60',
                    'X-File' => fn(SplFileInfo $file) => $file->getFilename(),
                ],
            ],
        );

        $res = $this->dispatch($middleware, '/style.css');

        self::assertSame('max-age=60', $res->getHeaderLine('Cache-Control'));
        self::assertSame('style.css', $res->getHeaderLine('X-File'));
    }

    #[Test]
    public function processDelegatesWhenFileDoesNotExist(): void
    {
        $res = $this->dispatch(new FileProcessMiddleware('vfs://public'), '/missing.css');

        self::assertSame(404, $res->getStatusCode());
        self::assertSame('next', (string) $res->getBody());
    }

    #[Test]
    public function processDelegatesForDirectories(): void
    {
        $res = $this->dispatch(new FileProcessMiddleware('vfs://public'), '/sub');

        self::assertSame(404, $res->getStatusCode());
    }

    #[Test]
    public function getMimeTypesReturnsInjectedInstanceOrDefault(): void
    {
        $custom = $this->createStub(MimeTypesInterface::class);

        self::assertSame($custom, (new FileProcessMiddleware('x', [], $custom))->getMimeTypes());
        self::assertInstanceOf(MimeTypes::class, (new FileProcessMiddleware('x'))->getMimeTypes());
    }
}
