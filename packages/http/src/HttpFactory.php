<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http;

use InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;
use Windwalker\Http\Factory\RequestFactory;
use Windwalker\Http\Factory\ResponseFactory;
use Windwalker\Http\Factory\ServerRequestFactory;
use Windwalker\Http\Factory\StreamFactory;
use Windwalker\Http\Factory\UploadedFileFactory;
use Windwalker\Uri\UriFactory;

use const UPLOAD_ERR_OK;

/**
 * The HttpFactory class.
 */
class HttpFactory extends UriFactory implements
    RequestFactoryInterface,
    ResponseFactoryInterface,
    ServerRequestFactoryInterface,
    StreamFactoryInterface,
    UploadedFileFactoryInterface
{
    protected RequestFactoryInterface $requestFactory;

    protected ServerRequestFactoryInterface $serverRequestFactory;

    protected ResponseFactoryInterface $responseFactory;

    protected StreamFactoryInterface $streamFactory;

    protected UploadedFileFactoryInterface $uploadedFileFactory;

    public static function create(): static
    {
        return new static();
    }

    public function __construct(
        ?RequestFactoryInterface $requestFactory = null,
        ?ServerRequestFactoryInterface $serverRequestFactory = null,
        ?ResponseFactoryInterface $responseFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?UploadedFileFactoryInterface $uploadedFileFactory = null,
    ) {
        $this->requestFactory = $requestFactory ?? new RequestFactory();
        $this->serverRequestFactory = $serverRequestFactory ?? new ServerRequestFactory();
        $this->responseFactory = $responseFactory ?? new ResponseFactory();
        $this->streamFactory = $streamFactory ?? new StreamFactory();
        $this->uploadedFileFactory = $uploadedFileFactory ?? new UploadedFileFactory();
    }

    /**
     * Create a new request.
     *
     * @param  string               $method  The HTTP method associated with the request.
     * @param  UriInterface|string  $uri     The URI associated with the request. If
     *                                       the value is a string, the factory MUST create a UriInterface
     *                                       instance based on it.
     *
     * @return RequestInterface
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        return $this->requestFactory->createRequest($uri, $method);
    }

    /**
     * Create a new response.
     *
     * @param  int     $code          HTTP status code; defaults to 200
     * @param  string  $reasonPhrase  Reason phrase to associate with status code
     *                                in generated response; if none is provided implementations MAY use
     *                                the defaults as suggested in the HTTP specification.
     *
     * @return ResponseInterface
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->responseFactory->createResponse($code, $reasonPhrase);
    }

    /**
     * Create a new server request.
     *
     * Note that server-params are taken precisely as given - no parsing/processing
     * of the given values is performed, and, in particular, no attempt is made to
     * determine the HTTP method or URI, which must be provided explicitly.
     *
     * @param  string               $method        The HTTP method associated with the request.
     * @param  UriInterface|string  $uri           The URI associated with the request. If
     *                                             the value is a string, the factory MUST create a UriInterface
     *                                             instance based on it.
     * @param  array                $serverParams  Array of SAPI parameters with which to seed
     *                                             the generated request instance.
     *
     * @return ServerRequestInterface
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return $this->serverRequestFactory->createServerRequest($method, $uri, $serverParams);
    }

    public function createServerRequestFromGlobals(): ServerRequestInterface
    {
        return ServerRequestFactory::createFromGlobals();
    }

    public function createServerRequestFromUri(UriInterface|string $uri): ServerRequestInterface
    {
        return ServerRequestFactory::createFromUri($uri);
    }

    /**
     * Create a new stream from a string.
     *
     * The stream SHOULD be created with a temporary resource.
     *
     * @param  string  $content  String content with which to populate the stream.
     *
     * @return StreamInterface
     */
    public function createStream(string $content = ''): StreamInterface
    {
        return $this->streamFactory->createStream($content);
    }

    /**
     * Create a stream from an existing file.
     *
     * The file MUST be opened using the given mode, which may be any mode
     * supported by the `fopen` function.
     *
     * The `$filename` MAY be any string supported by `fopen()`.
     *
     * @param  string  $filename  Filename or stream URI to use as basis of stream.
     * @param  string  $mode      Mode with which to open the underlying filename/stream.
     *
     * @return StreamInterface
     * @throws RuntimeException If the file cannot be opened.
     * @throws InvalidArgumentException If the mode is invalid.
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return $this->streamFactory->createStreamFromFile($filename, $mode);
    }

    /**
     * Create a new stream from an existing resource.
     *
     * The stream MUST be readable and may be writable.
     *
     * @param  resource  $resource  PHP resource to use as basis of stream.
     *
     * @return StreamInterface
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return $this->streamFactory->createStreamFromResource($resource);
    }

    /**
     * Create a new uploaded file.
     *
     * If a size is not provided it will be determined by checking the size of
     * the file.
     *
     * @see http://php.net/manual/features.file-upload.post-method.php
     * @see http://php.net/manual/features.file-upload.errors.php
     *
     * @param  StreamInterface  $stream           Underlying stream representing the
     *                                            uploaded file content.
     * @param  int|null         $size             in bytes
     * @param  int              $error            PHP file upload error
     * @param  string|null      $clientFilename   Filename as provided by the client, if any.
     * @param  string|null      $clientMediaType  Media type as provided by the client, if any.
     *
     * @return UploadedFileInterface
     *
     */
    public function createUploadedFile(
        StreamInterface $stream,
        int $size = null,
        int $error = UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFileInterface {
        return $this->uploadedFileFactory->createUploadedFile(
            $stream,
            $size,
            $error,
            $clientFilename,
            $clientMediaType
        );
    }
}
