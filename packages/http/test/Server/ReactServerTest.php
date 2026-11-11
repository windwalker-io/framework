<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Server;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\EventLoop\StreamSelectLoop;
use React\Http\HttpServer;
use React\Socket\SocketServer;
use RuntimeException;
use Windwalker\Http\Event\ErrorEvent;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\HttpFactory;
use Windwalker\Http\Server\ReactServer;

/**
 * Test class of ReactServer
 */
class ReactServerTest extends TestCase
{
    protected ReactServer $instance;

    #[Before]
    protected function createInstance(): void
    {
        if (!class_exists(SocketServer::class) || !interface_exists(LoopInterface::class)) {
            $this->markTestSkipped('react/socket and react/event-loop are required.');
        }

        $this->instance = new ReactServer();
    }

    #[After]
    protected function closeSocket(): void
    {
        $socket = $this->instance->socket;

        if ($socket !== null) {
            $socket->close();
        }
    }

    #[Test]
    public function isListeningIsFalseByDefault(): void
    {
        self::assertFalse($this->instance->isListening());
    }

    #[Test]
    public function listenThrowsWhenAlreadyListening(): void
    {
        $this->instance->listening = true;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('Server is listening.');

        $this->instance->listen('127.0.0.1', 0);
    }

    #[Test]
    public function getLoopCreatesLoopLazilyAndReusesIt(): void
    {
        $loop = $this->instance->getLoop();

        self::assertInstanceOf(LoopInterface::class, $loop);
        self::assertSame($loop, $this->instance->getLoop());
    }

    #[Test]
    public function setLoopOverridesLoop(): void
    {
        $loop = Loop::get();

        $this->instance->setLoop($loop);

        self::assertSame($loop, $this->instance->getLoop());
    }

    #[Test]
    public function createSocketBindsEphemeralPortOnLocalhost(): void
    {
        $socket = $this->instance->createSocket('127.0.0.1', 0);

        try {
            self::assertMatchesRegularExpression('#^tcp://127\.0\.0\.1:\d+$#', $socket->getAddress());
        } finally {
            $socket->close();
        }
    }

    #[Test]
    public function getSocketCreatesSocketOnceWhenNotSet(): void
    {
        $socket = $this->instance->getSocket();

        self::assertInstanceOf(SocketServer::class, $socket);
        self::assertSame($socket, $this->instance->getSocket());
    }

    #[Test]
    public function setSocketOverridesSocket(): void
    {
        $socket = $this->instance->createSocket('127.0.0.1', 0);

        $this->instance->setSocket($socket);

        self::assertSame($socket, $this->instance->getSocket());
    }

    #[Test]
    public function stopClosesSocketAndResetsState(): void
    {
        $socket = $this->instance->createSocket('127.0.0.1', 0);
        $this->instance->setSocket($socket);
        $this->instance->listening = true;

        $this->instance->stop();

        self::assertNull($socket->getAddress());
        self::assertNull($this->instance->socket);
        self::assertNull($this->instance->server);
        self::assertFalse($this->instance->isListening());
    }

    #[Test]
    public function resetClearsLoopSocketServerAndListeningFlag(): void
    {
        $this->instance->getLoop();
        $socket = $this->instance->getSocket();
        $this->instance->listening = true;

        $this->instance->reset();
        $socket->close();

        self::assertNull($this->instance->loop);
        self::assertNull($this->instance->socket);
        self::assertNull($this->instance->server);
        self::assertFalse($this->instance->isListening());
    }

    #[Test]
    public function getServerCreatesHttpServerOnceAndSetServerOverrides(): void
    {
        $server = $this->instance->getServer();

        self::assertInstanceOf(HttpServer::class, $server);
        self::assertSame($server, $this->instance->getServer());

        $other = new HttpServer(static fn() => null);
        $this->instance->setServer($other);

        self::assertSame($other, $this->instance->getServer());
    }

    #[Test]
    public function handlesRequestThroughRequestAndResponseEvents(): void
    {
        $names = [];

        $this->instance->on(
            'request',
            function (RequestEvent $event) use (&$names) {
                $names[] = 'request';
                $event->response = new HttpFactory()->createResponse(201)->withHeader('X-Path', $event->request->getUri()->getPath());
            },
        );
        $this->instance->on(
            'response',
            function (RequestEvent $event) use (&$names) {
                $names[] = 'response';
            },
        );

        $raw = $this->requestRaw("GET /foo HTTP/1.1\r\nHost: localhost\r\nConnection: close\r\n\r\n");

        self::assertStringStartsWith('HTTP/1.1 201', $raw);
        self::assertStringContainsString('X-Path: /foo', $raw);
        self::assertSame(['request', 'response'], $names);
    }

    #[Test]
    public function handleRunsMiddlewaresBeforeRequestEvent(): void
    {
        $this->instance->middleware(
            function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
                return $handler->handle($request->withAttribute('middleware', 'ran'));
            },
        );
        $this->instance->on(
            'request',
            function (RequestEvent $event) {
                $event->response = $this->instance
                    ->getHttpFactory()
                    ->createResponse()
                    ->withHeader('X-Middleware', $event->request->getAttribute('middleware'));
            },
        );

        $raw = $this->requestRaw("GET / HTTP/1.1\r\nHost: localhost\r\nConnection: close\r\n\r\n");

        self::assertStringStartsWith('HTTP/1.1 200', $raw);
        self::assertStringContainsString('X-Middleware: ran', $raw);
    }

    #[Test]
    public function returnsServerErrorWhenRequestListenerThrows(): void
    {
        $this->instance->on(
            'request',
            function () {
                throw new RuntimeException('boom');
            },
        );

        $raw = $this->requestRaw("GET / HTTP/1.1\r\nHost: localhost\r\nConnection: close\r\n\r\n");

        self::assertStringStartsWith('HTTP/1.1 500', $raw);
        self::assertStringContainsString('boom', $raw);
    }

    #[Test]
    public function returnsClientErrorCodeWhenListenerThrowsWithClientCode(): void
    {
        $this->instance->on(
            'request',
            function () {
                throw new RuntimeException('nope', 404);
            },
        );

        $raw = $this->requestRaw("GET / HTTP/1.1\r\nHost: localhost\r\nConnection: close\r\n\r\n");

        self::assertStringStartsWith('HTTP/1.1 404', $raw);
    }

    #[Test]
    public function emitsErrorEventWhenServerReportsError(): void
    {
        $caught = null;

        $this->instance->on(
            'error',
            function (ErrorEvent $event) use (&$caught) {
                $caught = $event->exception;
            },
        );

        $e = new RuntimeException('server error');
        $this->instance->getServer()->emit('error', [$e]);

        self::assertSame($e, $caught);
    }

    #[Test]
    public function listenRunsLoopUntilStopped(): void
    {
        $loop = new StreamSelectLoop();
        $this->instance->setLoop($loop);
        $loop->addTimer(0.01, function () {
            self::assertTrue($this->instance->isListening());
            $this->instance->stop();
        });

        $this->instance->listen('127.0.0.1', 0);

        self::assertFalse($this->instance->isListening());
    }

    private function requestRaw(string $request): string
    {
        $loop = new StreamSelectLoop();
        $this->instance->setLoop($loop);

        $socket = $this->instance->createSocket('127.0.0.1', 0);
        $this->instance->setSocket($socket);
        $this->instance->getServer()->listen($socket);

        $response = '';
        $connector = new \React\Socket\Connector([], $loop);
        $connector->connect($socket->getAddress())->then(
            function (\React\Socket\ConnectionInterface $conn) use ($request, &$response, $loop) {
                $conn->on('data', function ($chunk) use (&$response) {
                    $response .= $chunk;
                });
                $conn->on('close', fn() => $loop->stop());
                $conn->write($request);
            },
        );
        $loop->addTimer(5, fn() => $loop->stop());
        $loop->run();

        return $response;
    }
}
