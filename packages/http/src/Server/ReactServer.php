<?php

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Http\HttpServer;
use React\Socket\SocketServer;
use React\Socket\ServerInterface as ReactServerInterface;
use RuntimeException;
use Throwable;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Http\Event\ErrorEvent;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\Helper\ResponseHelper;
use Windwalker\Http\HttpFactory;
use Windwalker\Http\Middleware\RequestRunner;
use Windwalker\Http\Output\Output;
use Windwalker\Http\Request\ServerRequest;

use function Windwalker\DI\create;

/**
 * The ReactServerAdapter class.
 */
class ReactServer implements ServerInterface
{
    use EventAwareTrait;

    protected array $middlewares = [];

    protected ?Closure $middlewareResolver = null;

    protected ?HttpFactory $httpFactory = null;

    public ?LoopInterface $loop = null;

    /**
     * @var  ReactServerInterface|null
     */
    public ?ReactServerInterface $socket = null;

    public ?HttpServer $server = null;

    public bool $listening = false;

    /**
     * ReactServerAdapter constructor.
     */
    public function __construct()
    {
        //
    }

    protected function prepareServerLoop(?SocketServer $socket = null): LoopInterface
    {
        $server = $this->getServer();

        $server->listen($socket ?? $this->getSocket());

        return $this->getLoop();
    }

    public function listen(string $host = '0.0.0.0', int $port = 0, array $options = []): void
    {
        if ($this->listening) {
            throw new RuntimeException('Server is listening.');
        }

        $this->socket = $this->createSocket($host, $port);

        $loop = $this->prepareServerLoop($this->socket);

        $this->listening = true;

        $loop->run();
    }

    public function stop(): void
    {
        $this->getSocket()->close();
        $this->getLoop()->stop();
        $this->reset();
    }

    /**
     * @return  LoopInterface
     */
    public function getLoop(): LoopInterface
    {
        return $this->loop ??= Loop::get();
    }

    /**
     * @param  LoopInterface|null  $loop
     *
     * @return  static  Return self to support chaining.
     *
     * @codeCoverageIgnore
     */
    public function setLoop(?LoopInterface $loop): static
    {
        $this->loop = $loop;

        return $this;
    }

    /**
     * @return  ReactServerInterface
     */
    public function getSocket(): ReactServerInterface
    {
        return $this->socket ??= $this->createSocket('0.0.0.0', 0);
    }

    public function createSocket(string $host, int $port): SocketServer
    {
        return new SocketServer($host . ':' . $port, [], $this->getLoop());
    }

    /**
     * @param  ReactServerInterface|null  $socket
     *
     * @return  static  Return self to support chaining.
     *
     * @codeCoverageIgnore
     */
    public function setSocket(?ReactServerInterface $socket): static
    {
        $this->socket = $socket;

        return $this;
    }

    /**
     * @return  HttpServer
     */
    public function getServer(): HttpServer
    {
        return $this->server ??= $this->createServer();
    }

    protected function createServer(): HttpServer
    {
        $output = new Output();

        $server = new HttpServer(
            $this->getLoop(),
            function (ServerRequestInterface $req) use ($output) {
                try {
                    $middlewares = $this->getMiddlewares();
                    $middlewares[] = function (ServerRequestInterface $request) use ($output) {
                        $event = $this->emit(
                            new RequestEvent(
                                request: $request,
                                output: $output,
                            )->setName('request'),
                        );

                        return $event->response ?? $this->getHttpFactory()->createResponse();
                    };

                    $response = (new RequestRunner(
                        $middlewares,
                        $this->getMiddlewareResolver(),
                    ))->handle($req);
                } catch (Throwable $e) {
                    $code = $e->getCode();
                    $code = ResponseHelper::isClientError($code) ? $code : 500;

                    $res = $this->getHttpFactory()->createResponse($code);
                    $res->getBody()->write((string) $e);

                    return $res;
                }

                $event = $this->emit(
                    new RequestEvent(
                        request: $req,
                        output: $output,
                        response: $response,
                    )->setName('response'),
                );

                return $event->response;
            },
        );

        $server->on(
            'error',
            function (Throwable $e) use ($output) {
                $this->emit(
                    new ErrorEvent(
                        exception: $e,
                        request: new ServerRequest(), // Currently we send empty request
                        output: $output,
                    )->setName('error'),
                );
            },
        );

        return $server;
    }

    public function middleware(mixed $middleware, mixed ...$args): static
    {
        if (is_string($middleware)) {
            $middleware = create($middleware, ...$args);
        }

        $this->middlewares[] = $middleware;

        return $this;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function setMiddlewares(array $middlewares): static
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    public function getMiddlewareResolver(): Closure
    {
        return $this->middlewareResolver ?? static fn($entry) => $entry;
    }

    public function setMiddlewareResolver(?Closure $middlewareResolver): static
    {
        $this->middlewareResolver = $middlewareResolver;

        return $this;
    }

    public function getHttpFactory(): HttpFactory
    {
        return $this->httpFactory ??= new HttpFactory();
    }

    public function setHttpFactory(?HttpFactory $httpFactory): static
    {
        $this->httpFactory = $httpFactory;

        return $this;
    }

    /**
     * @param  HttpServer|null  $server
     *
     * @return  static  Return self to support chaining.
     *
     * @codeCoverageIgnore
     */
    public function setServer(?HttpServer $server): static
    {
        $this->server = $server;

        return $this;
    }

    public function reset(): void
    {
        $this->loop = null;
        $this->socket = null;
        $this->server = null;
        $this->listening = false;
    }

    /**
     * @return  bool
     */
    public function isListening(): bool
    {
        return $this->listening;
    }
}
