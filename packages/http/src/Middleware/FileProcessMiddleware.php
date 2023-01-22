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
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Mime\MimeTypesInterface;
use Windwalker\Filesystem\Path;
use Windwalker\Http\Response\Response;

/**
 * The FileProcessMiddleware class.
 */
class FileProcessMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected string $publicPath,
        protected $customMimes = [],
        protected ?MimeTypesInterface $mimeTypes = null
    ) {
        if (!interface_exists(MimeTypesInterface::class)) {
            throw new \DomainException('Please install symfony/mime first');
        }
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = ltrim($request->getUri()->getPath(), '/');

        $file = $this->publicPath . '/' . $path;

        if (is_file($file)) {
            $segments = explode('.', $file);
            $ext = array_pop($segments);
            $mime = $this->getMimeTypes()->getMimeTypes($ext)[0] ?? 'text/plain';

            return Response::readFrom($file)
                ->withHeader('Content-Type', $mime);
        }

        return $handler->handle($request);
    }

    /**
     * @return MimeTypesInterface
     */
    public function getMimeTypes(): MimeTypesInterface
    {
        return $this->mimeTypes ?? new MimeTypes($this->customMimes);
    }

    /**
     * @param  MimeTypesInterface|null  $mimeTypes
     *
     * @return  static  Return self to support chaining.
     */
    public function setMimeTypes(?MimeTypesInterface $mimeTypes): static
    {
        $this->mimeTypes = $mimeTypes;

        return $this;
    }
}
