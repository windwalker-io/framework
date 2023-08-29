<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Windwalker\Event\AbstractEvent;
use Windwalker\Http\Output\OutputInterface;

/**
 * The WebRequestEvent class.
 */
class RequestEvent extends AbstractEvent
{
    public ServerRequestInterface $request;

    public ?ResponseInterface $response = null;

    public OutputInterface $output;

    public ?\Closure $endHandler = null;

    public array $attributes = [];

    public int $fd = 0;

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @return ?ResponseInterface
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param  ResponseInterface  $response
     *
     * @return  static  Return self to support chaining.
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
     */
    public function setRequest(ServerRequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return $this->fd;
    }

    /**
     * @param  int  $fd
     *
     * @return  static  Return self to support chaining.
     */
    public function setFd(int $fd): static
    {
        $this->fd = $fd;

        return $this;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * @param  OutputInterface  $output
     *
     * @return  static  Return self to support chaining.
     */
    public function setOutput(OutputInterface $output): static
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param  array  $attributes
     *
     * @return  static  Return self to support chaining.
     */
    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function setAttribute(string $name, mixed $value): static
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function getAttribute(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @return \Closure|null
     */
    public function getEndHandler(): ?\Closure
    {
        return $this->endHandler;
    }

    /**
     * @param  \Closure|null  $endHandler
     *
     * @return  static  Return self to support chaining.
     */
    public function setEndHandler(?\Closure $endHandler): static
    {
        $this->endHandler = $endHandler;

        return $this;
    }
}
