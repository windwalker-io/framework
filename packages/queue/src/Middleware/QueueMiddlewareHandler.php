<?php

declare(strict_types=1);

namespace Windwalker\Queue\Middleware;

use Windwalker\Queue\Attributes\JobMiddlewares;
use Windwalker\Queue\Job\JobController;

class QueueMiddlewareHandler
{
    protected \Generator $queue;

    public static function createFromController(
        JobController $controller,
        \Closure $last,
    ): static {
        $job = $controller->job;

        // Find middlewares
        $ref = new \ReflectionObject($job);

        $middlewares = static function () use ($last, $job, $ref) {
            foreach ($ref->getMethods() as $method) {
                if ($method->getAttributes(JobMiddlewares::class, \ReflectionAttribute::IS_INSTANCEOF)) {
                    $items = $method->invoke($job);

                    foreach ($items as $item) {
                        yield $item;
                    }
                }

                yield $last;
            }
        };

        return new static($middlewares());
    }

    public function __construct(iterable $queue)
    {
        $this->queue = (static fn () => yield from $queue)();
    }

    public function handle(JobController $controller): JobController
    {
        $current = $this->queue->current();

        $this->queue->next();

        return $this->runMiddleware($current, $controller);
    }

    protected function runMiddleware(mixed $middleware, JobController $controller): mixed
    {
        if ($middleware instanceof \Closure) {
            return $middleware($controller);
        }

        if ($middleware instanceof QueueMiddlewareInterface) {
            return $middleware->process($controller, $this);
        }

        if ($middleware instanceof self) {
            return $middleware->handle($controller);
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Invalid middleware queue entry: %s. Middleware must implement %s or be an instance of %s.',
                $middleware,
                QueueMiddlewareInterface::class,
                self::class
            )
        );
    }
}
