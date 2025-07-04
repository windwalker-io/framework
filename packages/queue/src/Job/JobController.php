<?php

declare(strict_types=1);

namespace Windwalker\Queue\Job;

use Windwalker\Queue\Attributes\JobMiddlewareCallback;
use Windwalker\Queue\Attributes\JobMiddlewares;
use Windwalker\Queue\Middleware\QueueMiddlewareInterface;
use Windwalker\Queue\QueueMessage;

use function Windwalker\Queue\Middleware\;

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

    public JobWrapperInterface $job {
        get => $this->message->getRawJob();
    }

    public bool $failed {
        get => $this->exception !== null;
    }

    public ?\Throwable $exception = null;

    public ?int $releaseDelay = null;

    public \Closure $invoker;

    protected \Generator $middlewares;

    public function __construct(
        readonly public QueueMessage $message,
        ?\Closure $invoker = null,
    ) {
        $this->invoker = $invoker ?? fn(JobController $controller, callable $invokable, array $args = []) => $invokable($controller, ...$args);
    }

    public function release(int $delay = 0): void
    {
        $this->releaseDelay = $delay;
    }

    public function unrelease(): void
    {
        $this->releaseDelay = null;
    }

    // public function delete(): void
    // {
    //     $this->message->setDeleted(true);
    // }
    //
    // public function undelete(): void
    // {
    //     $this->message->setDeleted(false);
    // }

    /**
     * @template T
     *
     * @param  mixed            $job
     * @param  class-string<T>  $attrName
     *
     * @return  \Generator<array{ \ReflectionMethod, \ReflectionAttribute<T> }>
     */
    protected function findMethodsAttributes(mixed $job, string $attrName): \Generator
    {
        if (!is_object($job)) {
            return;
        }

        $ref = new \ReflectionObject($job);

        foreach ($ref->getMethods() as $method) {
            if ($attrs = $method->getAttributes($attrName, \ReflectionAttribute::IS_INSTANCEOF)) {
                yield $method->getName() => [$method, $attrs[0]];
            }
        }
    }

    protected function invokeMethodsWithAttribute(mixed $job, string $attrName): \Generator
    {
        foreach ($this->findMethodsAttributes($job, $attrName) as $name => [$method, $attr]) {
            yield $name => $this->invoke($method->getClosure($job));
        }
    }

    public function invoke(callable $invokable, array $args = [])
    {
        return ($this->invoker)($this, $invokable, $args);
    }

    public function failed(\Throwable|string $e, ?int $code = null): void
    {
        if (is_string($e)) {
            $e = new \RuntimeException($e, $code ?? 0);
        }

        $this->exception = $e;
    }

    public function success(): void
    {
        $this->exception = null;
    }

    public function run(): static
    {
        $this->compileMiddlewares(
            $this->job,
            // Todo: Maybe back to invokable object
            fn () => $this->invoke($this->job->process(...))
        );

        return $this->next();
    }

    protected function compileMiddlewares(mixed $job, \Closure $last): static
    {
        if (!is_object($job)) {
            return $this;
        }

        $middlewares = function () use ($last, $job) {
            $middlewares = [];
            $o = 0;

            foreach ($this->invokeMethodsWithAttribute($job, JobMiddlewares::class) as $items) {
                foreach ($items as $i => $item) {
                    $o++;
                    $middlewares[$i] = $item;
                }
            }

            foreach ($this->findMethodsAttributes($job, JobMiddlewareCallback::class) as $items) {
                foreach ($items as [$method, $attr]) {
                    /** @var JobMiddlewareCallback $attrInstance */
                    $attrInstance = $attr->newInstance();
                    $o++;

                    $middlewares[$attrInstance->order ?? $o] = $method->getClosure($job);
                }
            }

            ksort($middlewares);

            foreach ($middlewares as $middleware) {
                yield $middleware;
            }

            yield $last;
        };

        $this->middlewares = $middlewares();

        return $this;
    }

    public function next(): JobController
    {
        $current = $this->middlewares->current();

        $this->middlewares->next();

        return $this->runMiddleware($current);
    }

    protected function runMiddleware(mixed $middleware): mixed
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
