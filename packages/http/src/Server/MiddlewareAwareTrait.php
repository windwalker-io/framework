<?php

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Closure;

use function Windwalker\DI\create;

trait MiddlewareAwareTrait
{
    protected array $middlewares = [];

    protected ?Closure $middlewareResolver = null;

    public function middleware(mixed $middleware, mixed ...$args): static
    {
        if (is_string($middleware)) {
            if (!function_exists('Windwalker\DI\create')) {
                $middleware = static fn() => new $middleware(...$args);
            } else {
                $middleware = create($middleware, ...$args);
            }
        }

        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * @return  array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @param  array  $middlewares
     *
     * @return  static  Return self to support chaining.
     *
     * @codeCoverageIgnore
     */
    public function setMiddlewares(array $middlewares): static
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * @return  Closure
     */
    public function getMiddlewareResolver(): Closure
    {
        return $this->middlewareResolver ?? static fn($entry) => $entry;
    }

    /**
     * @param  Closure|null  $middlewareResolver
     *
     * @return  static  Return self to support chaining.
     */
    public function setMiddlewareResolver(?Closure $middlewareResolver): static
    {
        $this->middlewareResolver = $middlewareResolver;

        return $this;
    }
}
