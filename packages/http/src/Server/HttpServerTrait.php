<?php

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Psr\Http\Message\ServerRequestInterface;
use Windwalker\Event\AbstractEvent;
use Windwalker\Http\Event\ErrorEvent;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\Event\ResponseEvent;
use Windwalker\Http\Helper\ResponseHelper;
use Windwalker\Http\HttpFactory;
use Windwalker\Http\Middleware\RequestRunner;
use Windwalker\Http\Output\OutputInterface;
use Windwalker\Http\Output\StreamOutput;

use function Windwalker\DI\create;

/**
 * Trait HttpServerTrait
 */
trait HttpServerTrait
{
    protected \Closure|null $middlewareResolver = null;

    public function __construct(
        protected array $middlewares = [],
        protected ?HttpFactory $httpFactory = null,
        protected \Closure|null $outputBuilder = null,
    ) {
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
                    (new RequestEvent())
                        ->setRequest($req)
                        ->setOutput($output)
                );

                return $event->getResponse() ?? $this->getHttpFactory()->createResponse();
            };

            $runner = new RequestRunner($middlewares, $this->getMiddlewareResolver());

            $res = $runner->handle($request);

            /** @var ResponseEvent $event */
            $event = $this->emit(
                (new ResponseEvent())
                    ->setRequest($request)
                    ->setResponse($res)
                    ->setOutput($output)
                    ->setEndHandler($event?->getEndHandler())
                    ->setAttributes($event?->getAttributes() ?? [])
            );

            $endHandler = $event->getEndHandler();

            if ($event->getResponse()) {
                $output->respond($event->getResponse());
            }

            if ($endHandler) {
                $endHandler($output, $event->getResponse());
            }

            if ($output->isWritable()) {
                $output->close();
            }
        } catch (\Throwable $e) {
            $event = $this->emit(
                (new ErrorEvent())
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
            }
        );

        $this->on(
            ResponseEvent::class,
            function (ResponseEvent $event) {
                $this->eventPassthrough('reponse', $event);
            }
        );

        $this->on(
            ErrorEvent::class,
            function (ErrorEvent $event) {
                $this->eventPassthrough('error', $event);
            }
        );
    }

    protected function eventPassthrough(string $eventName, AbstractEvent $event): AbstractEvent
    {
        $this->emit($newEvent = $event->mirror($eventName));
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

    public function middleware(mixed $middleware, mixed ...$args): static
    {
        if (is_string($middleware)) {
            $middleware = create($middleware, ...$args);
        }

        $this->middlewares[] = $middleware;

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
        return $this->middlewareResolver ?? static fn($entry) => $entry;
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

    /**
     * @return \Closure|null
     */
    abstract public function getOutputBuilder(): ?\Closure;

    /**
     * @param  \Closure|null  $outputBuilder
     *
     * @return  static  Return self to support chaining.
     */
    public function setOutputBuilder(?\Closure $outputBuilder): static
    {
        $this->outputBuilder = $outputBuilder;

        return $this;
    }
}
