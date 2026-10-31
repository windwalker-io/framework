<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Middleware;

use ArrayIterator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Windwalker\Http\Factory\ServerRequestFactory;
use Windwalker\Http\Middleware\RequestHandler;
use Windwalker\Http\Middleware\RequestRunner;
use Windwalker\Http\Response\Response;

class RequestHandlerTest extends TestCase
{
    protected function createRequest(): ServerRequestInterface
    {
        return ServerRequestFactory::createFromUri('http://example.com/');
    }

    #[Test]
    public function constructorThrowsOnEmptyQueue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RequestHandler([]);
    }

    #[Test]
    public function constructorAcceptsTraversableQueue(): void
    {
        $handler = new RequestHandler(
            new ArrayIterator([fn() => Response::fromString('gen')]),
        );

        self::assertSame('gen', (string) $handler->handle($this->createRequest())->getBody());
    }

    #[Test]
    public function handleCallsCallableWithRequestAndHandler(): void
    {
        $handler = new RequestHandler([
            function (ServerRequestInterface $req, RequestHandlerInterface $next) {
                return $next->handle($req->withAttribute('a', '1'));
            },
            fn(ServerRequestInterface $req) => Response::fromString('attr:' . $req->getAttribute('a')),
        ]);

        self::assertSame('attr:1', (string) $handler->handle($this->createRequest())->getBody());
    }

    #[Test]
    public function handleProcessesPsrMiddlewareAndFinalRequestHandler(): void
    {
        $middleware = new class () implements MiddlewareInterface {
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler,
            ): ResponseInterface {
                return $handler->handle($request)->withHeader('X-Mw', 'yes');
            }
        };

        $final = new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return Response::fromString('final');
            }
        };

        $res = (new RequestHandler([$middleware, $final]))->handle($this->createRequest());

        self::assertSame('final', (string) $res->getBody());
        self::assertSame('yes', $res->getHeaderLine('X-Mw'));
    }

    #[Test]
    public function handleUsesResolverToConvertEntries(): void
    {
        $handler = new RequestHandler(
            ['hello'],
            fn(string $entry) => fn() => Response::fromString(strtoupper($entry)),
        );

        self::assertSame('HELLO', (string) $handler->handle($this->createRequest())->getBody());
    }

    #[Test]
    public function handleThrowsOnInvalidQueueEntry(): void
    {
        $handler = new RequestHandler(['not-callable']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid middleware queue entry: not-callable');

        $handler->handle($this->createRequest());
    }

    #[Test]
    public function invokeDelegatesToHandle(): void
    {
        $handler = new RequestHandler([fn() => Response::fromString('invoked')]);

        self::assertSame('invoked', (string) $handler($this->createRequest())->getBody());
    }

    #[Test]
    public function runnerRewindsQueueSoItCanRunRepeatedly(): void
    {
        $count = 0;

        $runner = new RequestRunner([
            function (ServerRequestInterface $req, RequestHandlerInterface $next) use (&$count) {
                $count++;

                return $next->handle($req);
            },
            fn() => Response::fromString('done'),
        ]);

        self::assertSame('done', (string) $runner->handle($this->createRequest())->getBody());
        self::assertSame('done', (string) $runner->handle($this->createRequest())->getBody());
        self::assertSame(2, $count);
    }
}
