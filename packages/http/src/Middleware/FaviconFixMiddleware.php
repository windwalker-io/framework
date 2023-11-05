<?php

declare(strict_types=1);

namespace Windwalker\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Windwalker\Http\Response\Response;

/**
 * The FaviconFixMiddleware class.
 */
class FaviconFixMiddleware implements MiddlewareInterface
{
    public function __construct(protected string $publicPath)
    {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();

        if (trim($uri->getPath(), '/\\') === 'favicon.ico') {
            $file = $this->publicPath . '/favicon.ico';

            if (is_file($file)) {
                return Response::readFrom($file)
                    ->withHeader('Content-Type', 'image/vnd.microsoft');
            }

            return Response::fromString('');
        }

        return $handler->handle($request);
    }
}
