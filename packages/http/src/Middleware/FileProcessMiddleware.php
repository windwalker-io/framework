<?php

declare(strict_types=1);

namespace Windwalker\Http\Middleware;

use DateTime;
use DomainException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplFileInfo;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Mime\MimeTypesInterface;
use Windwalker\Http\Response\Response;

use function Windwalker\value;

/**
 * The FileProcessMiddleware class.
 */
class FileProcessMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected string $publicPath,
        protected $customMimes = [],
        protected ?MimeTypesInterface $mimeTypes = null,
        protected array $options = [],
    ) {
        if (!interface_exists(MimeTypesInterface::class)) {
            throw new DomainException('Please install symfony/mime first');
        }
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = rawurldecode($request->getUri()->getPath());
        $path = str_replace('\\', '/', $path);

        if (in_array('..', explode('/', $path), true)) {
            return $handler->handle($request);
        }

        $file = new SplFileInfo($this->publicPath . '/' . ltrim($path, '/'));

        if (is_file($file->getPathname())) {
            $segments = explode('.', $file->getPathname());
            $ext = array_pop($segments);
            $mime = $this->getMimeTypes()->getMimeTypes($ext)[0] ?? 'text/plain';

            $modified = $file->getMTime();

            $res = Response::readFrom($file->getPathname())
                ->withHeader('Content-Type', $mime)
                ->withHeader(
                    'Last-modified',
                    DateTime::createFromFormat('U', (string) $modified)->format(DateTime::RFC850),
                );

            foreach ($this->options['headers'] ?? [] as $header => $value) {
                $value = value($value, $file);

                $res = $res->withHeader($header, $value);
            }

            return $res;
        }

        return $handler->handle($request);
    }

    /**
     * @return  MimeTypesInterface
     */
    public function getMimeTypes(): MimeTypesInterface
    {
        return $this->mimeTypes ?? new MimeTypes($this->customMimes);
    }

    /**
     * @param  MimeTypesInterface|null  $mimeTypes
     *
     * @return  static  Return self to support chaining.
     *
     * @codeCoverageIgnore
     */
    public function setMimeTypes(?MimeTypesInterface $mimeTypes): static
    {
        $this->mimeTypes = $mimeTypes;

        return $this;
    }
}
