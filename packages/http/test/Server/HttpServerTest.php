<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Server;

use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Windwalker\Http\Event\ErrorEvent;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\Event\ResponseEvent;
use Windwalker\Http\Factory\ServerRequestFactory;
use Windwalker\Http\HttpFactory;
use Windwalker\Http\Output\StreamOutput;
use Windwalker\Http\Response\Response;
use Windwalker\Http\Server\HttpServer;
use Windwalker\Http\Server\PhpServer;
use Windwalker\Http\Test\Mock\MockOutput;

class HttpServerTest extends TestCase
{
    protected MockOutput $output;

    protected function createPhpServer(array $middlewares = []): PhpServer
    {
        return new PhpServer(
            $middlewares,
            null,
            fn() => $this->output = new MockOutput(),
        );
    }

    protected function request(): ServerRequestInterface
    {
        return ServerRequestFactory::createFromUri('http://example.com/foo');
    }

    #[Test]
    public function constructorDefaultsAdapterToPhpServer(): void
    {
        $server = new HttpServer();

        self::assertInstanceOf(PhpServer::class, $server->getAdapter());
    }

    #[Test]
    public function setAdapterReplacesAdapter(): void
    {
        $server = new HttpServer();
        $adapter = new PhpServer();

        self::assertSame($server, $server->setAdapter($adapter));
        self::assertSame($adapter, $server->getAdapter());
    }

    #[Test]
    public function listenUsesDefaultHandlerAndMergesOptions(): void
    {
        $server = new HttpServer(['foo' => 'bar'], $this->createPhpServer());

        $captured = null;

        $server->setHandler(
            function ($adapter, $host, $port, $options) use (&$captured) {
                $captured = [$host, $port, $options];
            },
        );

        $server->listen('127.0.0.1', 8080, ['baz' => 'qux']);

        self::assertSame('127.0.0.1', $captured[0]);
        self::assertSame(8080, $captured[1]);
        self::assertEquals(['foo' => 'bar', 'baz' => 'qux'], $captured[2]);
    }

    #[Test]
    public function listenDefaultHandlerDelegatesToAdapterListen(): void
    {
        $adapter = $this->createPhpServer();
        $adapter->onRequest(fn(RequestEvent $e) => $e->response = Response::fromString('adapter'));

        $server = new HttpServer([], $adapter);
        $server->listen('0.0.0.0', 80, ['request' => $this->request()]);

        self::assertSame('adapter', (string) $this->output->output);
    }

    #[Test]
    public function stopDelegatesToAdapter(): void
    {
        $server = new HttpServer([], $this->createPhpServer());
        $server->stop();

        self::assertTrue(true);
    }

    #[Test]
    public function onRequestAndOnResponseListenToLegacyEventNames(): void
    {
        $server = new HttpServer([], $this->createPhpServer());
        $called = [];

        $server->onRequest(function (RequestEvent $e) use (&$called) {
            $called[] = 'request';
            $e->response = Response::fromString('x');
        });
        $server->onResponse(function () use (&$called) {
            $called[] = 'response';
        });

        $server->getAdapter()->handle($this->request());

        self::assertSame(['request', 'response'], $called);
    }

    #[Test]
    public function handleRunsMiddlewaresBeforeRequestEvent(): void
    {
        $server = $this->createPhpServer([
            function (ServerRequestInterface $req, RequestHandlerInterface $next) {
                return $next->handle($req->withAttribute('mw', 'ok'));
            },
        ]);

        $server->onRequest(function (RequestEvent $e) {
            $e->response = Response::fromString('mw:' . $e->request->getAttribute('mw'));
        });

        $server->handle($this->request());

        self::assertSame('mw:ok', (string) $this->output->output);
    }

    #[Test]
    public function handleUsesDefaultResponseWhenNoListenerSetsOne(): void
    {
        $server = $this->createPhpServer();
        $server->handle($this->request());

        self::assertSame('', (string) $this->output->output);
    }

    #[Test]
    public function handleCallsEndHandlerAndResponseListeners(): void
    {
        $server = $this->createPhpServer();
        $ended = null;
        $seenResponse = null;

        $server->onRequest(function (RequestEvent $e) use (&$ended) {
            $e->response = Response::fromString('body');
            $e->endHandler = function ($output, $res) use (&$ended) {
                $ended = (string) $res->getBody();
            };
        });
        $server->onResponse(function (ResponseEvent $e) use (&$seenResponse) {
            $seenResponse = (string) $e->response->getBody();
        });

        $server->handle($this->request());

        self::assertSame('body', $ended);
        self::assertSame('body', $seenResponse);
    }

    #[Test]
    public function handleEmitsLegacyResponseEvent(): void
    {
        $server = $this->createPhpServer();
        $called = false;

        $server->on('response', function () use (&$called) {
            $called = true;
        });
        $server->onRequest(function (RequestEvent $event) {
            $event->response = Response::fromString('body');
        });

        $server->handle($this->request());

        self::assertTrue($called);
    }

    #[Test]
    public function handleDispatchesErrorEventAndSwallowsWhenPropagationStopped(): void
    {
        $server = $this->createPhpServer();

        $server->onRequest(function () {
            throw new RuntimeException('boom');
        });

        $caught = null;
        $server->onError(function (ErrorEvent $e) use (&$caught) {
            $caught = $e->exception->getMessage();
            $e->stopPropagation();
        });

        $server->handle($this->request());

        self::assertSame('boom', $caught);
    }

    #[Test]
    public function handleRethrowsExceptionWhenErrorEventNotStopped(): void
    {
        $server = $this->createPhpServer();
        $server->onRequest(function () {
            throw new LogicException('unhandled', 404);
        });

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('unhandled');

        $server->handle($this->request());
    }

    #[Test]
    public function errorResponseUsesClientErrorCodeOrFallsBackTo500(): void
    {
        $statuses = [];

        foreach ([404, 0] as $code) {
            $server = $this->createPhpServer();
            $server->onRequest(function () use ($code) {
                throw new RuntimeException('x', $code);
            });
            $server->onError(function (ErrorEvent $e) use (&$statuses) {
                $statuses[] = $e->response->getStatusCode();
                $e->stopPropagation();
            });

            $server->handle($this->request());
        }

        self::assertSame([404, 500], $statuses);
    }

    #[Test]
    public function middlewareAcceptsInstanceAndAppendsToQueue(): void
    {
        $server = new PhpServer();
        $mw = fn() => Response::fromString('x');

        self::assertSame($server, $server->middleware($mw));
        self::assertSame([$mw], $server->getMiddlewares());
    }

    #[Test]
    public function setMiddlewareResolverIsUsedWhenRunningQueue(): void
    {
        $server = $this->createPhpServer(['name']);
        $server->setMiddlewareResolver(
            fn(string $name) => fn(ServerRequestInterface $req, RequestHandlerInterface $next)
                => Response::fromString('resolved:' . $name),
        );

        $server->handle($this->request());

        self::assertSame('resolved:name', (string) $this->output->output);
    }

    #[Test]
    public function getMiddlewareResolverDefaultsToIdentity(): void
    {
        $server = new PhpServer();

        self::assertSame('a', $server->getMiddlewareResolver()('a'));

        $server->setMiddlewareResolver(null);

        self::assertSame('b', $server->getMiddlewareResolver()('b'));
    }

    #[Test]
    public function httpFactoryDefaultsAndCanBeReplaced(): void
    {
        $server = new PhpServer();

        self::assertInstanceOf(HttpFactory::class, $server->getHttpFactory());

        $factory = new HttpFactory();
        $server->setHttpFactory($factory);

        self::assertSame($factory, $server->getHttpFactory());
    }

    #[Test]
    public function outputBuilderDefaultsToStreamOutputAndCanBeReplaced(): void
    {
        $server = new PhpServer();

        self::assertInstanceOf(StreamOutput::class, $server->createOutput());

        $server->setOutputBuilder(fn() => new MockOutput());

        self::assertInstanceOf(MockOutput::class, $server->createOutput());
    }
}
