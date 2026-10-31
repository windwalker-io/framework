<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Middleware;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Windwalker\Http\Factory\ServerRequestFactory;
use Windwalker\Http\Middleware\FaviconFixMiddleware;
use Windwalker\Http\Response\Response;

class FaviconFixMiddlewareTest extends TestCase
{
    #[Before]
    protected function setUpVirtualFilesystem(): void
    {
        vfsStream::setup('public', null, ['favicon.ico' => 'ICONDATA']);
    }

    private function handler(): RequestHandlerInterface
    {
        return new class () implements RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
            {
                return Response::fromString('next', 201);
            }
        };
    }

    #[Test]
    public function processServesExistingFavicon(): void
    {
        $middleware = new FaviconFixMiddleware('vfs://public');

        $res = $middleware->process(
            ServerRequestFactory::createFromUri('http://example.com/favicon.ico'),
            $this->handler(),
        );

        self::assertSame('ICONDATA', (string) $res->getBody());
        self::assertSame('image/vnd.microsoft', $res->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function processReturnsEmptyResponseWhenFaviconMissing(): void
    {
        $middleware = new FaviconFixMiddleware('vfs://public/missing');

        $res = $middleware->process(
            ServerRequestFactory::createFromUri('http://example.com/favicon.ico'),
            $this->handler(),
        );

        self::assertSame(200, $res->getStatusCode());
        self::assertSame('', (string) $res->getBody());
    }

    #[Test]
    public function processDelegatesOtherPathsToHandler(): void
    {
        $middleware = new FaviconFixMiddleware('vfs://public');

        $res = $middleware->process(
            ServerRequestFactory::createFromUri('http://example.com/other'),
            $this->handler(),
        );

        self::assertSame(201, $res->getStatusCode());
        self::assertSame('next', (string) $res->getBody());
    }
}
