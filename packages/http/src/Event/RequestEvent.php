<?php

declare(strict_types=1);

namespace Windwalker\Http\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Windwalker\Event\BaseEvent;
use Windwalker\Http\Output\OutputInterface;

/**
 * The WebRequestEvent class.
 */
class RequestEvent extends BaseEvent
{
    public function __construct(
        public ServerRequestInterface $request,
        public OutputInterface $output,
        public ?ResponseInterface $response = null,
        public ?\Closure $endHandler = null,
        public array $attributes = [],
        public int $fd = 0,
    ) {
        //
    }

    /**
     * @return ServerRequestInterface
     *
     * @deprecated  Use property instead.
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @return ?ResponseInterface
     *
     * @deprecated  Use property instead.
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param  ResponseInterface  $response
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setResponse(ResponseInterface $response): static
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @param  ServerRequestInterface  $request
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setRequest(ServerRequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return int
     *
     * @deprecated  Use property instead.
     */
    public function getFd(): int
    {
        return $this->fd;
    }

    /**
     * @param  int  $fd
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setFd(int $fd): static
    {
        $this->fd = $fd;

        return $this;
    }

    /**
     * @return OutputInterface
     *
     * @deprecated  Use property instead.
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * @param  OutputInterface  $output
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setOutput(OutputInterface $output): static
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @return array
     *
     * @deprecated  Use property instead.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param  array  $attributes
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return  $this
     *
     * @deprecated  Use property instead.
     */
    public function setAttribute(string $name, mixed $value): static
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param  string  $name
     *
     * @return  mixed
     *
     * @deprecated  Use property instead.
     */
    public function getAttribute(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @return \Closure|null
     *
     * @deprecated  Use property instead.
     */
    public function getEndHandler(): ?\Closure
    {
        return $this->endHandler;
    }

    /**
     * @param  \Closure|null  $endHandler
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setEndHandler(?\Closure $endHandler): static
    {
        $this->endHandler = $endHandler;

        return $this;
    }
}
