<?php

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory as ReactFactory;
use React\EventLoop\LoopInterface;
use React\Http\Server;
use React\Socket\Server as SocketServer;
use React\Socket\ServerInterface as ReactServerInterface;
use RuntimeException;
use Throwable;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Http\Event\ErrorEvent;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\Helper\ResponseHelper;
use Windwalker\Http\HttpFactory;
use Windwalker\Http\Output\Output;
use Windwalker\Http\Request\ServerRequest;

/**
 * The ReactServerAdapter class.
 */
class ReactServer implements ServerInterface
{
    use EventAwareTrait;

    public ?LoopInterface $loop = null;

    /**
     * @var ReactServerInterface|null
     */
    public ?ReactServerInterface $socket = null;

    public ?Server $server = null;

    public bool $listening;

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
     * @return LoopInterface
     */
    public function getLoop(): LoopInterface
    {
        return $this->loop ??= ReactFactory::create();
    }

    /**
     * @param  LoopInterface|null  $loop
     *
     * @return  static  Return self to support chaining.
     */
    public function setLoop(?LoopInterface $loop): static
    {
        $this->loop = $loop;

        return $this;
    }

    /**
     * @return ReactServerInterface
     */
    public function getSocket(): ReactServerInterface
    {
        return $this->socket ??= $this->createSocket('0.0.0.0', 0);
    }

    public function createSocket(string $host, int $port): SocketServer
    {
        return new SocketServer($host . ':' . $port, $this->getLoop());
    }

    /**
     * @param  ReactServerInterface|null  $socket
     *
     * @return  static  Return self to support chaining.
     */
    public function setSocket(?ReactServerInterface $socket): static
    {
        $this->socket = $socket;

        return $this;
    }

    /**
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->server ??= $this->createServer();
    }

    protected function createServer(): Server
    {
        $output = new Output();

        $server = new Server(
            $this->getLoop(),
            function (ServerRequestInterface $req) use ($output) {
                try {
                    $event = $this->emit(
                        new RequestEvent(
                            request: $req,
                            output: $output,
                        )->setName('request')
                    );
                } catch (Throwable $e) {
                    $code = $e->getCode();
                    $code = ResponseHelper::isClientError($code) ? $code : 500;

                    $res = new HttpFactory()->createResponse($code);
                    $res->getBody()->write((string) $e);

                    return $res;
                }

                $event = $this->emit(
                    new RequestEvent(
                        request: $req,
                        output: $output,
                        response: $event->response,
                    )->setName('response'),
                );

                return $event->response;
            }
        );

        $server->on(
            'error',
            function (Throwable $e) use ($output) {
                $this->emit(
                    new ErrorEvent(
                        exception: $e,
                        request: new ServerRequest(), // Currently we send empty request
                        output: $output,
                    )->setName('error')
                );
            }
        );

        return $server;
    }

    /**
     * @param  Server|null  $server
     *
     * @return  static  Return self to support chaining.
     */
    public function setServer(?Server $server): static
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
     * @return bool
     */
    public function isListening(): bool
    {
        return $this->listening;
    }
}
