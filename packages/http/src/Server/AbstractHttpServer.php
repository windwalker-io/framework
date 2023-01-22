<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Windwalker\Http\Event\ErrorEvent;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\Event\ResponseEvent;
use Windwalker\Http\Helper\ResponseHelper;
use Windwalker\Http\HttpFactory;
use Windwalker\Http\Middleware\RequestRunner;
use Windwalker\Http\Output\OutputInterface;

/**
 * The AbstractHttpServer class.
 */
abstract class AbstractHttpServer extends AbstractServer implements HttpServerInterface
{
    protected \Closure|null $middlewareResolver = null;

    public function __construct(
        protected array $middlewares = [],
        protected ?OutputInterface $output = null,
        protected ?HttpFactory $httpFactory = null,
    ) {
        //
    }

    protected function handleRequest(ServerRequestInterface $request, OutputInterface $output): void
    {
        try {
            $middlewares = $this->getMiddlewares();
            $middlewares[] = fn ($req) => $this->run($req, $output);

            $runner = new RequestRunner($middlewares, $this->getMiddlewareResolver());

            $res = $runner->handle($request);

            $output->respond($res);
        } catch (\Throwable $e) {
            $event = $this->emit(
                (new ErrorEvent('error'))
                    ->setException($e)
                    ->setRequest($request)
                    ->setResponse(
                        $this->getHttpFactory()->createResponse(
                            ResponseHelper::isClientError($e->getCode()) ? $e->getCode() : 500
                        )
                    )
                    ->setOutput($output)
            );

            if (!$event->isPropagationStopped()) {
                throw $event->getException();
            }
        }
    }

    public function run(ServerRequestInterface $request, OutputInterface $output): ResponseInterface
    {
        /** @var RequestEvent $event */
        $event = $this->emit(
            RequestEvent::wrap('request')
                ->setRequest($request)
                ->setOutput($output)
                ->setMiddlewares($this->getMiddlewares())
        );

        $event = $this->emit(
            ResponseEvent::wrap('response')
                ->setRequest($event->getRequest())
                ->setResponse($event->getResponse() ?? $this->getHttpFactory()->createResponse())
                ->setOutput($output)
        );

        return $event->getResponse();
    }

    public function onRequest(callable $listener, ?int $priority = null): static
    {
        $this->on('request', $listener, $priority);

        return $this;
    }

    public function onResponse(callable $listener, ?int $priority = null): static
    {
        $this->on('response', $listener, $priority);

        return $this;
    }

    public function onError(callable $listener, ?int $priority = null): static
    {
        $this->on('error', $listener, $priority);

        return $this;
    }

    /**
     * Method to get property Output
     *
     * @return  ?OutputInterface
     */
    public function getOutput(): ?OutputInterface
    {
        return $this->output;
    }

    /**
     * Method to set property output
     *
     * @param  ?OutputInterface  $output
     *
     * @return  static  Return self to support chaining.
     */
    public function setOutput(?OutputInterface $output): static
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @return HttpFactory
     */
    public function getHttpFactory(): HttpFactory
    {
        return $this->httpFactory ??= new HttpFactory();
    }

    /**
     * @param  HttpFactory|null  $httpFactory
     *
     * @return  static  Return self to support chaining.
     */
    public function setHttpFactory(?HttpFactory $httpFactory): static
    {
        $this->httpFactory = $httpFactory;

        return $this;
    }

    /**
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @param  array  $middlewares
     *
     * @return  static  Return self to support chaining.
     */
    public function setMiddlewares(array $middlewares): static
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * @return \Closure
     */
    public function getMiddlewareResolver(): \Closure
    {
        return $this->middlewareResolver ?? static fn ($entry) => $entry;
    }

    /**
     * @param  \Closure|null  $middlewareResolver
     *
     * @return  static  Return self to support chaining.
     */
    public function setMiddlewareResolver(?\Closure $middlewareResolver): static
    {
        $this->middlewareResolver = $middlewareResolver;

        return $this;
    }
}
