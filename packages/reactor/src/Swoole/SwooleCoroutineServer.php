<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole;

use Swoole\Coroutine\Http\Server as SwooleServer;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Windwalker\DI\Container;
use Windwalker\Http\Server\AbstractHttpServer;

use function Windwalker\run;

/**
 * The SwooleCoroutineServer class.
 */
class SwooleCoroutineServer extends AbstractHttpServer
{
    protected ?string $host = null;

    protected array $config = [];

    protected bool $ssl = false;

    protected bool $reusePort = false;

    protected ?SwooleServer $swooleServer = null;

    public function listen(string $host = '0.0.0.0', int $port = 0, array $options = []): void
    {
        $server = $this->getSwooleServer(
            $host,
            $port,
            $this->ssl,
            $this->reusePort
        );

        $this->startListen($server);

        run(
            function () use ($server) {
                $server->start();
            }
        );
    }

    protected function startListen(SwooleServer $server): void
    {
        $server->handle(
            '/',
            function (Request $request, Response $response) {
                $psrRequest = SwooleRequestFactory::createPsrFromSwooleRequest($request, $this->getHost());
                $fd = $request->fd;

                $psrRequest = $psrRequest->withAttribute(
                    'swoole',
                    compact('fd', 'request', 'response')
                );

                $output = $this->createOutput($response);

                $this->handleRequest($psrRequest, $output);

                gc_collect_cycles();
            }
        );
    }

    public function stop(int $workerId = -1, bool $waitEvent = false): void
    {
        $this->getSwooleServer()->stop($workerId, $waitEvent);
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function getSwooleServer(
        string $host = '0.0.0.0',
        int $port = 0,
        bool $ssl = false,
        bool $reusePort = false
    ): SwooleServer {
        if (!$this->swooleServer) {
            $server = static::createSwooleServer($host, $port, $ssl, $reusePort);
            $server->set($this->config);

            $this->swooleServer = $server;
        }

        return $this->swooleServer;
    }

    public static function createSwooleServer(
        string $host = '0.0.0.0',
        int $port = 0,
        bool $ssl = false,
        bool $reusePort = false
    ): SwooleServer {
        return new SwooleServer($host, $port, $ssl, $reusePort);
    }

    /**
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @param  string|null  $host
     *
     * @return  static  Return self to support chaining.
     */
    public function setHost(?string $host): static
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return \Closure|null
     */
    public function getOutputBuilder(): ?\Closure
    {
        return $this->outputBuilder ??= static function (Response $response) {
            return new SwooleOutput($response);
        };
    }

    public static function factory(
        array $config = [],
        array $middlewares = [],
        bool $ssl = false,
        bool $reusePort = false,
    ): \Closure {
        return static function (Container $container) use ($middlewares, $config, $ssl, $reusePort) {
            $server = $container->newInstance(static::class);
            $server->setSsl($ssl);
            $server->setReusePort($reusePort);
            $server->setConfig($config);
            $server->setMiddlewares($middlewares);
            $server->setMiddlewareResolver(
                function ($entry) use ($container) {
                    if ($entry instanceof \Closure) {
                        return $entry;
                    }

                    return $container->resolve($entry);
                }
            );

            return $server;
        };
    }

    public function isSsl(): bool
    {
        return $this->ssl;
    }

    /**
     * @param  bool  $ssl
     *
     * @return  static  Return self to support chaining.
     */
    public function setSsl(bool $ssl): static
    {
        $this->ssl = $ssl;

        return $this;
    }

    public function isReusePort(): bool
    {
        return $this->reusePort;
    }

    /**
     * @param  bool  $reusePort
     *
     * @return  static  Return self to support chaining.
     */
    public function setReusePort(bool $reusePort): static
    {
        $this->reusePort = $reusePort;

        return $this;
    }
}
