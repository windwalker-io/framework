<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Server as SwooleBaseServer;
use Swoole\Server\Port;
use Swoole\WebSocket\Frame;
use Windwalker\DI\Container;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\Server\HttpServerTrait;
use Windwalker\Http\Server\ServerInterface;
use Windwalker\Reactor\Protocol;
use Windwalker\Reactor\Swoole\Event\AfterReloadEvent;
use Windwalker\Reactor\Swoole\Event\BeforeHandshakeResponseEvent;
use Windwalker\Reactor\Swoole\Event\BeforeReloadEvent;
use Windwalker\Reactor\Swoole\Event\BeforeShutdownEvent;
use Windwalker\Reactor\Swoole\Event\CloseEvent;
use Windwalker\Reactor\Swoole\Event\ConnectEvent;
use Windwalker\Reactor\Swoole\Event\DisconnectEvent;
use Windwalker\Reactor\Swoole\Event\FinishEvent;
use Windwalker\Reactor\Swoole\Event\HandshakeEvent;
use Windwalker\Reactor\Swoole\Event\ManagerStartEvent;
use Windwalker\Reactor\Swoole\Event\ManagerStopEvent;
use Windwalker\Reactor\Swoole\Event\MessageEvent;
use Windwalker\Reactor\Swoole\Event\OpenEvent;
use Windwalker\Reactor\Swoole\Event\PacketEvent;
use Windwalker\Reactor\Swoole\Event\PipeMessageEvent;
use Windwalker\Reactor\Swoole\Event\ReceiveEvent;
use Windwalker\Reactor\Swoole\Event\ShutdownEvent;
use Windwalker\Reactor\Swoole\Event\StartEvent;
use Windwalker\Reactor\Swoole\Event\TaskEvent;
use Windwalker\Reactor\Swoole\Event\WorkerErrorEvent;
use Windwalker\Reactor\Swoole\Event\WorkerExitEvent;
use Windwalker\Reactor\Swoole\Event\WorkerStartEvent;
use Windwalker\Reactor\Swoole\Event\WorkerStopEvent;
use Windwalker\Reactor\WebSocket\MessageEmitterInterface;
use Windwalker\Reactor\WebSocket\WebSocketRequest;
use Windwalker\Reactor\WebSocket\WebSocketServerInterface;
use Windwalker\Utilities\Exception\ExceptionFactory;
use Windwalker\Utilities\StrNormalize;
use Windwalker\Utilities\TypeCast;

/**
 * The SwooleTcpServer class.
 *
 * @formatter:off
 *
 * @method $this onStart(callable $handler)
 * @method $this onBeforeShutdown(callable $handler)
 * @method $this onShutdown(callable $handler)
 * @method $this onWorkerStart(callable $handler)
 * @method $this onWorkerStop(callable $handler)
 * @method $this onWorkerExit(callable $handler)
 * @method $this onConnect(callable $handler)
 * @method $this onReceive(callable $handler)
 * @method $this onPacket(callable $handler)
 * @method $this onClose(callable $handler)
 * @method $this onTask(callable $handler)
 * @method $this onFinish(callable $handler)
 * @method $this onPipeMessage(callable $handler)
 * @method $this onWorkerError(callable $handler)
 * @method $this onManagerStart(callable $handler)
 * @method $this onManagerStop(callable $handler)
 * @method $this onBeforeReload(callable $handler)
 * @method $this onAfterReload(callable $handler)
 *
 * @method $this onRequest(callable $handler)
 * @method $this onOpen(callable $handler)
 * @method $this onBeforeHandshakeResponse(callable $handler)
 * @method $this onHandshake(callable $handler)
 * @method $this onMessage(callable $handler)
 * @method $this onDisconnect(callable $handler)
 *
 * @method bool reload(bool $onlyReloadTaskWorker = false)
 * @method bool shutdown()
 * @method void tick(int $millisecond, callable $callback)
 * @method void after(int $millisecond, callable $callback)
 * @method bool clearTimer(int $timerId)
 * @method bool close(int $fd, bool $reset = false)
 * @method bool send(int|string $fd, string $data, int $serverSocket = -1)
 * @method bool sendfile(int $fd, string $filename, int $offset = 0, int $length = 0)
 * @method bool sendMessage(mixed $message, int $workerId)
 * @method bool exist(int $fd)
 * @method array stats()
 * @method int task(mixed $data, int $dstWorkerId = -1, callable $finishCallback = null)
 * @method mixed taskwait(mixed $data, float $timeout = 0.5, int $dstWorkerId = -1)
 * @method false|array taskWaitMulti(array $tasks, float $timeout = 0.5)
 * @method false|array taskCo(array $tasks, float $timeout = 0.5)
 * @method bool finish(mixed $data)
 * @method int|false getWorkerId()
 * @method int|false getWorkerPid(int $worker_id = -1)
 * @method int|false getWorkerStatus(int $worker_id = -1)
 * @method int getManagerPid()
 * @method int getMasterPid()
 *
 * phpcs:ignore
 * @method int push(int $fd, Frame|string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN)
 * @method string pack(Frame|string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN)
 * @method Frame|false unpack(string $data)
 * @method bool disconnect(int $fd, int $code = SWOOLE_WEBSOCKET_CLOSE_NORMAL, string $reason = '')
 * @method bool isEstablished(int $fd)
 *
 * @formatter:on
 */
class SwooleServer implements ServerInterface, WebSocketServerInterface
{
    use EventAwareTrait;
    use HttpServerTrait;

    protected ?string $host = null;

    protected array $config = [];

    protected int $mode = SWOOLE_BASE;

    protected int $sockType = SWOOLE_TCP;

    protected SwooleBaseServer|Port|null $swooleServer = null;

    protected bool $isSubServer = false;

    public array $eventMapping = [
        'Start' => StartEvent::class,
        'BeforeShutdown' => BeforeShutdownEvent::class,
        'Shutdown' => ShutdownEvent::class,
        'WorkerStart' => WorkerStartEvent::class,
        'WorkerStop' => WorkerStopEvent::class,
        'WorkerExit' => WorkerExitEvent::class,
        'Connect' => ConnectEvent::class,
        'Receive' => ReceiveEvent::class,
        'Packet' => PacketEvent::class,
        'Close' => CloseEvent::class,
        'Task' => TaskEvent::class,
        'Finish' => FinishEvent::class,
        'PipeMessage' => PipeMessageEvent::class,
        'WorkerError' => WorkerErrorEvent::class,
        'ManagerStart' => ManagerStartEvent::class,
        'ManagerStop' => ManagerStopEvent::class,
        'BeforeReload' => BeforeReloadEvent::class,
        'AfterReload' => AfterReloadEvent::class,

        // HTTP
        'Request' => RequestEvent::class,

        // Websocket
        'BeforeHandshakeResponse' => BeforeHandshakeResponseEvent::class,
        'Handshake' => HandshakeEvent::class,
        'Open' => OpenEvent::class,
        'Message' => MessageEvent::class,
        'Disconnect' => DisconnectEvent::class,
    ];

    /**
     * @var array<static>
     */
    protected array $subServers = [];

    protected \Closure $listenCallback;

    protected MessageEmitterInterface $messageEmitter;

    public function listen(string $host = '0.0.0.0', int $port = 0, array $options = []): void
    {
        if ($this->isSubServer) {
            $this->listenCallback = function (SwooleBaseServer $parentServer) use ($port, $host) {
                $serverPort = $parentServer->listen($host, $port, $this->sockType);
                $this->swooleServer = $serverPort;

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

    protected static function keysToCamel(array $args): array
    {
        $newArgs = [];

        foreach ($args as $name => $value) {
            if (str_contains($name, '_')) {
                $name = StrNormalize::toCamelCase($name);
            }

            $newArgs[$name] = $value;
        }

        return $newArgs;
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

    public function set(string $key, mixed $value): static
    {
        $this->config[$key] = $value;

        return $this;
    }

    public function getSwooleServer(
        string $host = '0.0.0.0',
        int $port = 0,
        int $mode = SWOOLE_BASE,
        int $sockType = SWOOLE_SOCK_TCP
    ): SwooleBaseServer|Port {
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
    ): SwooleBaseServer {
        $class = static::getSwooleServerClassName();

        return new $class($host, $port, $mode, $sockType);
    }

    protected static function getSwooleServerClassName(): string
    {
        return SwooleBaseServer::class;
    }

    public function createSubServer(int $protocol = 0): static
    {
        $subServer = clone $this;
        $subServer->isSubServer = true;
        $subServer->swooleServer = null;

        $this->subServers[] = $subServer;

        if ($protocol > 0) {
            $this->setProtocols($protocol);
        }

        return $subServer;
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

    public function isSubServer(): bool
    {
        return $this->isSubServer;
    }

    /**
     * @param  int  $protocol
     *
     * @return  void
     */
    public function setProtocols(int $protocol): void
    {
        $this->set('open_http_protocol', (bool) ($protocol & Protocol::HTTP));
        $this->set('open_http2_protocol', (bool) ($protocol & Protocol::HTTP2));
        $this->set('open_websocket_protocol', (bool) ($protocol & Protocol::WEBSOCKET));
        $this->set('open_mqtt_protocol', (bool) ($protocol & Protocol::MQTT));
    }

    public function getServersInfo(): array
    {
        if ($this->isSubServer) {
            throw new \LogicException(__METHOD__ . ' cannot call on sub server.');
        }

        $servers = [];
        $swooleServer = $this->getSwooleServer();

        $servers[] = [
            'class' => $swooleServer::class,
            'host' => $swooleServer->host,
            'port' => $swooleServer->port,
            'mode' => $this->mode,
            'sockType' => $this->sockType,
            'config' => $swooleServer->setting,
        ];

        foreach ($this->subServers as $subServer) {
            $swooleServer = $subServer->getSwooleServer();

            $servers[] = [
                'class' => $swooleServer::class,
                'host' => $swooleServer->host,
                'port' => $swooleServer->port,
                'mode' => $subServer->mode,
                'sockType' => $subServer->sockType,
                'config' => $swooleServer->setting,
            ];
        }

        return $servers;
    }

    public function __call(string $name, array $args)
    {
        if (str_starts_with($name, 'on')) {
            $event = substr($name, 2);

            if ($this->eventMapping[$event] ?? null) {
                $eventClass = $this->eventMapping[$event];

                return $this->on($eventClass, ...$args);
            }
        }

        if (method_exists(SwooleBaseServer::class, $name)) {
            if (!$this->swooleServer) {
                throw new \BadMethodCallException('You must call this method after swoole server started.');
            }

            return $this->swooleServer->$name(...$args);
        }

        throw ExceptionFactory::badMethodCall($name);
    }

    protected function registerEvents(SwooleBaseServer|Port $port): void
    {
        $this->registerBaseEvents($port);
        $this->registerHttpEvents($port);
        $this->registerWebsocketEvents($port);
    }

    protected function proxySwooleEvent(
        SwooleBaseServer|Port $port,
        string $eventName,
        callable $handler
    ): void {
        $eventClass = $this->eventMapping[$eventName] ?? null;

        if (!$eventClass) {
            throw new \LogicException(
                "Event mapping not found for: $eventName"
            );
        }

        $hasListeners = TypeCast::toArray($this->dispatcher->getListeners($eventClass)) !== [];

        if ($hasListeners) {
            $port->on($eventName, $handler);
        }
    }

    /**
     * @param  Port|SwooleBaseServer  $port
     *
     * @return  void
     */
    protected function registerBaseEvents(Port|SwooleBaseServer $port): void
    {
        $server = $this;

        $this->proxySwooleEvent(
            $port,
            'Start',
            function (SwooleBaseServer $swooleServer) use ($server) {
                return $this->emit(
                    StartEvent::class,
                    compact(
                        'swooleServer',
                        'server'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'BeforeShutdown',
            function (SwooleBaseServer $swooleServer) use ($server) {
                $this->emit(
                    BeforeShutdownEvent::class,
                    compact(
                        'swooleServer',
                        'server'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'Shutdown',
            function (SwooleBaseServer $swooleServer) use ($server) {
                $this->emit(
                    BeforeShutdownEvent::class,
                    compact(
                        'swooleServer',
                        'server'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'WorkerStart',
            function (SwooleBaseServer $swooleServer, int $workerId) use ($server) {
                $this->emit(
                    WorkerStartEvent::class,
                    compact(
                        'swooleServer',
                        'server',
                        'workerId'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'WorkerStop',
            function (SwooleBaseServer $swooleServer, int $workerId) use ($server) {
                $this->emit(
                    WorkerStopEvent::class,
                    compact(
                        'swooleServer',
                        'server',
                        'workerId'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'WorkerExit',
            function (SwooleBaseServer $swooleServer, int $workerId) use ($server) {
                $this->emit(
                    WorkerExitEvent::class,
                    compact(
                        'swooleServer',
                        'server',
                        'workerId'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'Connect',
            function (SwooleBaseServer $swooleServer, int $reactorId) use ($server) {
                $this->emit(
                    ConnectEvent::class,
                    compact(
                        'swooleServer',
                        'server',
                        'reactorId'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'Receive',
            function (SwooleBaseServer $swooleServer, int $reactorId, string $data) use ($server) {
                $this->emit(
                    ReceiveEvent::class,
                    compact(
                        'swooleServer',
                        'server',
                        'reactorId',
                        'data'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'Packet',
            function (SwooleBaseServer $swooleServer, string $data, array $clientInfo) use ($server) {
                $this->emit(
                    PacketEvent::class,
                    compact(
                        'swooleServer',
                        'server',
                        'data',
                        'clientInfo'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'Close',
            function (SwooleBaseServer $swooleServer, int $reactorId) use ($server) {
                $this->emit(
                    CloseEvent::class,
                    compact(
                        'swooleServer',
                        'server',
                        'reactorId'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'Task',
            function (SwooleBaseServer $swooleServer, int $taskId, int $srcWorkerId, mixed $data) use ($server) {
                $this->emit(
                    TaskEvent::class,
                    compact(
                        'swooleServer',
                        'server',
                        'taskId',
                        'srcWorkerId',
                        'data'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'Finish',
            function (SwooleBaseServer $swooleServer, int $taskId, mixed $data) use ($server) {
                $this->emit(
                    FinishEvent::class,
                    compact(
                        'swooleServer',
                        'server',
                        'taskId',
                        'data'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'PipeMessage',
            function (SwooleBaseServer $swooleServer, int $srcWorkerId, mixed $message) use ($server) {
                $this->emit(
                    PipeMessageEvent::class,
                    compact(
                        'swooleServer',
                        'server',
                        'srcWorkerId',
                        'message'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'WorkerError',
            function (
                SwooleBaseServer $swooleServer,
                int $workerId,
                int $workerPid,
                int $exitCode,
                int $signal
            ) use (
                $server
            ) {
                $this->emit(
                    PipeMessageEvent::class,
                    compact(
                        'swooleServer',
                        'server',
                        'workerId',
                        'workerPid',
                        'exitCode',
                        'signal'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'ManagerStart',
            function (SwooleBaseServer $swooleServer) use ($server) {
                $this->emit(
                    ManagerStartEvent::class,
                    compact(
                        'swooleServer',
                        'server'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'ManagerStop',
            function (SwooleBaseServer $swooleServer) use ($server) {
                $this->emit(
                    ManagerStopEvent::class,
                    compact(
                        'swooleServer',
                        'server'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'BeforeReload',
            function (SwooleBaseServer $swooleServer) use ($server) {
                $this->emit(
                    BeforeReloadEvent::class,
                    compact(
                        'swooleServer',
                        'server'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'AfterReload',
            function (SwooleBaseServer $swooleServer) use ($server) {
                $this->emit(
                    AfterReloadEvent::class,
                    compact(
                        'swooleServer',
                        'server'
                    )
                );
            }
        );
    }

    /**
     * @param  Port|SwooleBaseServer  $port
     *
     * @return  void
     */
    protected function registerHttpEvents(Port|SwooleBaseServer $port): void
    {
        $this->proxySwooleEvent(
            $port,
            'Request',
            function (Request $request, Response $response) {
                $psrRequest = SwooleRequestFactory::createPsrFromSwooleRequest($request, $this->getHost());
                $fd = $request->fd;
                $server = $this;
                $swooleServer = $this;

                $psrRequest = $psrRequest->withAttribute(
                    'swoole',
                    compact('fd', 'request', 'response', 'server', 'swooleServer')
                );

                $output = $this->createOutput($response);

                $this->handleRequest($psrRequest, $output);
            }
        );
    }

    protected function registerWebsocketEvents(Port|SwooleBaseServer $port): void
    {
        $server = $this;

        $this->proxySwooleEvent(
            $port,
            'BeforeHandshakeResponse',
            function (SwooleBaseServer $swooleServer, Request $request, Response $response) use ($server) {
                $this->emit(
                    BeforeHandshakeResponseEvent::class,
                    compact(
                        'swooleServer',
                        'server',
                        'request',
                        'response'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'Handshake',
            function (Request $request, Response $response) {
                $this->emit(
                    HandshakeEvent::class,
                    compact(
                        'request',
                        'response'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'Open',
            function (SwooleBaseServer $swooleServer, Request $swooleRequest) use ($server) {
                $request = WebSocketRequest::createFromSwooleRequest($swooleRequest);
                $this->emit(
                    OpenEvent::class,
                    compact(
                        'swooleServer',
                        'server',
                        'request',
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'Message',
            function (SwooleBaseServer $swooleServer, Frame $swooleFrame) use ($server) {
                $frame = new WebSocketFrameWrapper($swooleFrame);

                $this->emit(
                    MessageEvent::class,
                    compact(
                        'swooleServer',
                        'server',
                        'frame'
                    )
                );
            }
        );

        $this->proxySwooleEvent(
            $port,
            'Disconnect',
            function (SwooleBaseServer $swooleServer, int $fd) use ($server) {
                $this->emit(
                    DisconnectEvent::class,
                    compact(
                        'swooleServer',
                        'server',
                        'fd'
                    )
                );
            }
        );
    }

    public static function factory(
        array $config = [],
        array $middlewares = [],
        ?int $mode = null,
        ?int $sockType = null,
    ): \Closure {
        return static function (Container $container) use ($middlewares, $config, $mode, $sockType) {
            $server = $container->newInstance(static::class);
            $server->setMode($mode ?? SWOOLE_PROCESS);
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

    public function getMessageEmitter(): MessageEmitterInterface
    {
        if (!$this->swooleServer) {
            throw new \RuntimeException('Swoole server not exists to get message emitter');
        }

        return $this->messageEmitter ??= new SwooleMessageTextEmitter($this->swooleServer);
    }

    /**
     * @param  MessageEmitterInterface  $messageEmitter
     *
     * @return  static  Return self to support chaining.
     */
    public function setMessageEmitter(MessageEmitterInterface $messageEmitter): static
    {
        $this->messageEmitter = $messageEmitter;

        return $this;
    }
}
