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
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\Event\ResponseEvent;
use Windwalker\Http\HttpFactory;
use Windwalker\Http\Request\ServerRequestFactory;

/**
 * The SwooleServer class.
 */
class SwooleHttpServer extends AbstractServer
{
    protected ?string $host = null;

    protected int $mode = SWOOLE_BASE;

    protected int $sockType = SWOOLE_TCP;

    protected ?SwooleServer $swooleServer = null;

    protected HttpFactory $httpFactory;

    public function __construct(?HttpFactory $httpFactory = null)
    {
        $this->httpFactory = $httpFactory ?? new HttpFactory();
    }

    public function setConfig(array $config): bool
    {
        return $this->swooleServer->set($config);
    }

    public function listen(string $host = '0.0.0.0', int $port = 0, array $options = []): void
    {
        $server = $this->getSwooleServer($host, $port, $this->mode, $this->sockType);

        $server->on(
            'request',
            function (Request $request, Response $response) {
                $req = ServerRequestFactory::createFromSwooleRequest($request, $this->getHost());

                $event = $this->emit(
                    RequestEvent::wrap('request')
                        ->setRequest($req)
                );

                /** @var ResponseEvent $event */
                $event = $this->emit(
                    ResponseEvent::wrap('response')
                        ->setRequest($event->getRequest())
                        ->setResponse($event->getResponse() ?? $this->httpFactory->createResponse())
                );

                $res = $event->getResponse();

                $response->status($res->getStatusCode(), $res->getReasonPhrase());

                foreach ($res->getHeaders() as $header => $values) {
                    $response->header($header, $res->getHeaderLine($header));
                }

                $response->end((string) $res->getBody());
            }
        );

        $server->start();
    }

    public function stop(int $workerId = -1, bool $waitEvent = false): void
    {
        $this->getSwooleServer()->stop($workerId, $waitEvent);
    }

    public function getSwooleServer(
        string $host = '0.0.0.0',
        int $port = 0,
        int $mode = SWOOLE_BASE,
        int $sockType = SWOOLE_SOCK_TCP
    ): SwooleServer {
        return $this->swooleServer ??= static::createSwooleServer($host, $port, $mode, $sockType);
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
}
