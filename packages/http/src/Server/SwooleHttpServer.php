<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as SwooleServer;
use Swoole\Server\Port;
use Windwalker\Core\CliServer\CliServerState;
use Windwalker\DI\Container;
use Windwalker\Http\Event\Swoole\StartEvent;
use Windwalker\Http\Factory\ServerRequestFactory;
use Windwalker\Http\HttpFactory;
use Windwalker\Http\Output\SwooleOutput;

/**
 * The SwooleServer class.
 */
class SwooleHttpServer extends AbstractHttpServer
{
    protected ?string $host = null;

    protected array $config = [];

    protected int $mode = SWOOLE_BASE;

    protected int $sockType = SWOOLE_TCP;

    protected ?SwooleServer $swooleServer = null;

    protected bool $isSubServer = false;

    /**
     * @var array<static>
     */
    protected array $subServers = [];

    protected \Closure $listenCallback;

    public function createSubServer(
        array $middlewares = [],
        ?HttpFactory $httpFactory = null,
        \Closure|null $outputBuilder = null,
    ): static {
        $subServer = new static(
            $middlewares,
            $httpFactory ?? $this->httpFactory,
            $outputBuilder ?? $this->outputBuilder
        );
        $subServer->isSubServer = true;

        $this->subServers[] = $subServer;

        return $subServer;
    }

    public function listen(string $host = '0.0.0.0', int $port = 0, array $options = []): void
    {
        if ($this->isSubServer) {
            $this->listenCallback = function (SwooleServer $parentServer, array &$servers = []) use ($port, $host) {
                $serverPort = $parentServer->listen($host, $port, $this->sockType);

                $this->registerEvents($serverPort);
            };
        } else {
            $servers = [];

            $server = $this->getSwooleServer($host, $port, $this->mode, $this->sockType);

            $this->registerEvents($server);

            foreach ($this->subServers as $subServer) {
                ($subServer->listenCallback)($server, $servers);
            }

            $server->start();
        }
    }

    protected function registerEvents(SwooleServer|Port $server): void
    {
        $server->on(
            'start',
            function (SwooleServer $server) {
                $this->emit(
                    StartEvent::class,
                    [
                        'swooleServer' => $server,
                        'httpServer' => $this,
                    ]
                );
            }
        );

        $server->on(
            'request',
            function (Request $request, Response $response) {
                $psrRequest = ServerRequestFactory::createFromSwooleRequest($request, $this->getHost());
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

        // Todo: support tasks and workers
    }

    public function onStart(callable $handler): static
    {
        $this->on(StartEvent::class, $handler);

        return $this;
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
        int $mode = SWOOLE_BASE,
        int $sockType = SWOOLE_SOCK_TCP
    ): SwooleServer {
        if (!$this->swooleServer) {
            $server = static::createSwooleServer($host, $port, $mode, $sockType);
            $server->set($this->config);

            $this->swooleServer = $server;
        }

        return $this->swooleServer;
    }

    public static function createSwooleServer(
        string $host = '0.0.0.0',
        int $port = 0,
        int $mode = SWOOLE_BASE,
        int $sockType = SWOOLE_SOCK_TCP
    ): SwooleServer {
        return new SwooleServer($host, $port, $mode, $sockType);
    }

    /**
     * @return int
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * @param  int  $mode
     *
     * @return  static  Return self to support chaining.
     */
    public function setMode(int $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @return int
     */
    public function getSockType(): int
    {
        return $this->sockType;
    }

    /**
     * @param  int  $sockType
     *
     * @return  static  Return self to support chaining.
     */
    public function setSockType(int $sockType): static
    {
        $this->sockType = $sockType;

        return $this;
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
        ?int $mode = null,
        ?int $sockType = null,
    ): \Closure {
        return static function (Container $container) use ($middlewares, $config, $mode, $sockType) {
            $server = $container->newInstance(static::class);
            $server->setMode($mode ?? SWOOLE_BASE);
            $server->setSockType($sockType ?? SWOOLE_TCP);
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

    public function isSubServer(): bool
    {
        return $this->isSubServer;
    }

    public function getServersInfo(): array
    {
        $servers = [];
        $swooleServer = $this->getSwooleServer();

        $servers[] = [
            'class' => $swooleServer::class,
            'host' => $swooleServer->host,
            'port' => $swooleServer->port,
            'mode' => $this->mode,
            'sockType' => $this->sockType,
            'config' => $this->config,
            'middlewares' => $this->getMiddlewares(),
        ];

        foreach ($this->subServers as $subServer) {
            $swooleServer = $subServer->getSwooleServer();

            $servers[] = [
                'class' => $swooleServer::class,
                'host' => $swooleServer->host,
                'port' => $swooleServer->port,
                'mode' => $subServer->mode,
                'sockType' => $subServer->sockType,
                'config' => $subServer->config,
                'middlewares' => $subServer->getMiddlewares(),
            ];
        }

        return $servers;
    }
}
