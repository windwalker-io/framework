<?php

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Windwalker\Event\EventInterface;
use Windwalker\Http\Event\ErrorEvent;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\Event\ResponseEvent;
use Windwalker\Http\Helper\ResponseHelper;
use Windwalker\Http\HttpFactory;
use Windwalker\Http\Middleware\RequestRunner;
use Windwalker\Http\Output\OutputInterface;

/**
 * Trait HttpServerTrait
 */
trait HttpServerTrait
{
    use MiddlewareAwareTrait;

    public function __construct(
        array $middlewares = [],
        protected ?HttpFactory $httpFactory = null,
        protected ?Closure $outputBuilder = null,
    ) {
        $this->middlewares = $middlewares;
        $this->adaptLegacyEvents();
    }

    protected function handleRequest(ServerRequestInterface $request, OutputInterface $output): void
    {
        try {
            $event = null;

            $middlewares = $this->getMiddlewares();

            $middlewares[] = function (ServerRequestInterface $req) use ($output, &$event) {
                /** @var RequestEvent $event */
                $event = $this->emit(
                    new RequestEvent(
                        request: $req,
                        output: $output,
                    ),
                );

                return $event->response ?? $this->getHttpFactory()->createResponse();
            };

            $runner = new RequestRunner($middlewares, $this->getMiddlewareResolver());

            $res = $runner->handle($request);

            /** @var ResponseEvent $event */
            $event = $this->emit(
                new ResponseEvent(
                    request: $request,
                    output: $output,
                    response: $res,
                    endHandler: $event?->endHandler,
                    attributes: $event?->attributes ?? [],
                ),
            );

            $endHandler = $event->endHandler;

            if ($event->response) {
                $output->respond($event->response);
            }

            if ($endHandler) {
                $endHandler($output, $event->response);
            }

            if ($output->isWritable()) {
                $output->close();
            }
        } catch (Throwable $e) {
            $event = $this->emit(
                new ErrorEvent(
                    exception: $e,
                    request: $request,
                    output: $output,
                    response: $this->getHttpFactory()->createResponse(
                        ResponseHelper::isClientError($e->getCode()) ? $e->getCode() : 500,
                    ),
                ),
            );

            if (!$event->isPropagationStopped()) {
                throw $event->exception;
            }
        }
    }

    public function onRequest(callable $listener, ?int $priority = null): static
    {
        $this->on(RequestEvent::class, $listener, $priority);

        return $this;
    }

    public function onResponse(callable $listener, ?int $priority = null): static
    {
        $this->on(ResponseEvent::class, $listener, $priority);

        return $this;
    }

    public function onError(callable $listener, ?int $priority = null): static
    {
        $this->on(ErrorEvent::class, $listener, $priority);

        return $this;
    }

    public function adaptLegacyEvents(): void
    {
        $this->on(
            RequestEvent::class,
            function (RequestEvent $event) {
                $this->eventPassthrough('request', $event);
            },
        );

        $this->on(
            ResponseEvent::class,
            function (ResponseEvent $event) {
                $this->eventPassthrough('response', $event);
            },
        );

        $this->on(
            ErrorEvent::class,
            function (ErrorEvent $event) {
                $this->eventPassthrough('error', $event);
            },
        );
    }

    protected function eventPassthrough(string $eventName, EventInterface $event): EventInterface
    {
        $newEvent = clone $event;
        $newEvent->setName($eventName);

        $this->emit($newEvent);
        $event->merge($newEvent->getArguments());

        return $newEvent;
    }

    /**
     * Method to get property Output
     *
     * @param  mixed  ...$args
     *
     * @return  OutputInterface
     */
    public function createOutput(...$args): OutputInterface
    {
        return $this->getOutputBuilder()(...$args);
    }

    /**
     * @return  HttpFactory
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
     * @return  Closure|null
     */
    abstract public function getOutputBuilder(): ?Closure;

    /**
     * @param  Closure|null  $outputBuilder
     *
     * @return  static  Return self to support chaining.
     */
    public function setOutputBuilder(?Closure $outputBuilder): static
    {
        $this->outputBuilder = $outputBuilder;

        return $this;
    }
}
