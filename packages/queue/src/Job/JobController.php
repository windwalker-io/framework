<?php

declare(strict_types=1);

namespace Windwalker\Queue\Job;

use Windwalker\Queue\Attributes\JobEntry;
use Windwalker\Queue\Attributes\JobMiddleware;
use Windwalker\Queue\Attributes\JobMiddlewaresProvider;
use Windwalker\Queue\Middleware\QueueMiddlewareInterface;
use Windwalker\Queue\QueueMessage;
use Windwalker\Utilities\Attributes\AttributesAccessor;
use Windwalker\Utilities\Iterator\PriorityQueue;

/**
 * @psalm-type RunnerCallback = \Closure(JobController $controller): mixed
 */
// phpcs:disable
class JobController
{
    public int $attempts {
        get => $this->message->getAttempts();
    }

    public string|int $id {
        get => $this->message->getId();
    }

    public int $delay {
        get => $this->message->getDelay();
    }

    public bool $deleted {
        get => $this->message->isDeleted();
    }

    public array $body {
        get => $this->message->getBody();
    }

    public object $job {
        get => $this->message->getJob();
    }

    public bool $failed {
        get => $this->exception !== null;
    }

    public ?\Throwable $exception = null;

    public ?int $releaseDelay = null;

    public bool $maxAttemptsExceeded = false;

    public \Closure $invoker;

    protected \Generator $middlewares;

    /**
     * @psalm-var 'pending'|'middleware'|'running'
     */
    protected string $context = 'pending';

    public function __construct(
        readonly public QueueMessage $message,
        ?\Closure $invoker = null,
    ) {
        $this->invoker = $invoker ?? fn(JobController $controller, callable $invokable, array $args = []) => $invokable(
            $controller,
            ...$args
        );
    }

    public function release(int $delay = 0): static
    {
        $this->releaseDelay = $delay;

        return $this;
    }

    public function unrelease(): static
    {
        $this->releaseDelay = null;

        return $this;
    }

    public function failed(\Throwable|string $e, ?int $code = null): static
    {
        if (is_string($e)) {
            $e = new \RuntimeException($e, $code ?? 0);
        }

        $this->exception = $e;

        return $this;
    }

    public function success(): static
    {
        $this->exception = null;

        return $this;
    }

    // }

    /**
     * @template T
     *
     * @param  class-string<T>  $attrName
     *
     * @return  \Generator<array{ \ReflectionMethod, \ReflectionAttribute<T> }>
     */
    protected function findMethodsAttributes(string $attrName): \Generator
    {
        if (!is_object($this->job)) {
            return;
        }

        foreach (new \ReflectionObject($this->job)->getMethods() as $method) {
            if ($attrs = $method->getAttributes($attrName, \ReflectionAttribute::IS_INSTANCEOF)) {
                yield $method->getName() => [$method, $attrs[0]];
            }
        }
    }

    public function invokeMethodsWithAttribute(string $attrName, ...$args): \Generator
    {
        foreach ($this->findMethodsAttributes($attrName) as $name => [$method, $attr]) {
            yield $name => $this->invoke($method->getClosure($this->job), $args);
        }
    }

    public function invoke(callable $invokable, array $args = [])
    {
        return ($this->invoker)($this, $invokable, $args);
    }

    public function run(): static
    {
        if ($this->context !== 'pending') {
            throw new \RuntimeException(
                sprintf('JobController is already in %s context, cannot run again.', $this->context)
            );
        }

        if (is_callable($this->job)) {
            // Job is invokable, we can call it directly.
            $last = function () {
                $this->invoke($this->job);

                return $this;
            };
        } else {
            // Find JobEntry Attribute
            $entry = $this->findMethodsAttributes(JobEntry::class)->current();

            if (!$entry) {
                throw new \RuntimeException(
                    sprintf(
                        'Job %s must have a method with %s attribute or be invokable.',
                        get_debug_type($this->job),
                        JobEntry::class
                    )
                );
            }

            /** @var \ReflectionMethod $method */
            $method = $entry[0];
            $last = function () use ($method) {
                $this->context = 'running';

                $this->invoke($method->getClosure($this->job));

                return $this;
            };
        }

        $this->compileMiddlewares(
            $this->job,
            $last
        );

        $this->context = 'middleware';

        $controller = $this->next();

        $this->context = 'pending';

        return $controller;
    }

    public function next(): JobController
    {
        if ($this->context !== 'middleware') {
            throw new \RuntimeException(
                sprintf('JobController is already in %s context, cannot run next middleware.', $this->context)
            );
        }

        $current = $this->middlewares->current();

        $this->middlewares->next();

        return $this->invokeMiddleware($current);
    }

    protected function compileMiddlewares(mixed $job, \Closure $last): static
    {
        if (!is_object($job)) {
            return $this;
        }

        $middlewares = function () use ($last, $job) {
            $middlewares = new PriorityQueue();

            // Get middlewares from JobMiddlewaresProvider attributes.
            foreach ($this->invokeMethodsWithAttribute(JobMiddlewaresProvider::class) as $methodName => $items) {
                if (!is_iterable($items)) {
                    throw new \RuntimeException(
                        sprintf(
                            '%s::%s() must return an iterable of middleware list, %s given.',
                            get_debug_type($this->job),
                            $methodName,
                            get_debug_type($items)
                        )
                    );
                }

                foreach ($items as $i => $item) {
                    $attr = AttributesAccessor::getFirstAttributeInstance(
                        $item,
                        JobMiddleware::class,
                        \ReflectionAttribute::IS_INSTANCEOF
                    );

                    $middlewares->insert($item, $attr?->order ?? $i);
                }
            }

            $o = $middlewares->count() - 1;

            // Get middlewares from JobMiddleware attributes.
            foreach ($this->findMethodsAttributes(JobMiddleware::class) as [$method, $attr]) {
                /** @var JobMiddleware $attrInstance */
                $attrInstance = $attr->newInstance();
                $o++;

                $middlewares->insert($method->getClosure($job), $attrInstance->order ?? $o);
            }

            $middlewares = array_reverse($middlewares->toArray());

            foreach ($middlewares as $middleware) {
                yield $middleware;
            }

            yield $last;
        };

        $this->middlewares = $middlewares();

        return $this;
    }

    protected function invokeMiddleware(mixed $middleware): mixed
    {
        if ($middleware instanceof \Closure) {
            return $middleware($this);
        }

        if ($middleware instanceof QueueMiddlewareInterface) {
            return $middleware->process($this);
        }

        if ($middleware instanceof self) {
            return $middleware->next();
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
