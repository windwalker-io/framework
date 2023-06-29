<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The Request Handler class.
 */
class RequestHandler extends AbstractRequestHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $entry      = current($this->queue);
        $middleware = ($this->resolver)($entry);
        next($this->queue);

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        }

        if ($middleware instanceof RequestHandlerInterface) {
            return $middleware->handle($request);
        }

        if (is_callable($middleware)) {
            return $middleware($request, $this);
        }

        throw new \RuntimeException(
            sprintf(
                'Invalid middleware queue entry: %s. Middleware must either be callable or implement %s.',
                $middleware,
                MiddlewareInterface::class
            )
        );
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handle($request);
    }
}
