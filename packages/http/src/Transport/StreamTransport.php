<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Transport;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use UnexpectedValueException;
use Windwalker\Http\Exception\HttpRequestException;
use Windwalker\Http\File\HttpUploadFileInterface;
use Windwalker\Http\Helper\HeaderHelper;
use Windwalker\Http\Helper\MultipartHelper;
use Windwalker\Http\HttpClientInterface;
use Windwalker\Http\Response\Response;
use Windwalker\Http\Stream\RequestBodyStream;
use Windwalker\Stream\Stream;
use Windwalker\Stream\StreamHelper;
use Windwalker\Utilities\Arr;

use const Windwalker\Stream\READ_ONLY_FROM_BEGIN;

/**
 * The StreamTransport class.
 *
 * @since  2.1
 */
class StreamTransport extends AbstractTransport
{
    /**
     * Send a request to the server and return a Response object with the response.
     *
     * @param  RequestInterface  $request  The request object to store request params.
     *
     * @param  array             $options
     *
     * @return  ResponseInterface
     *
     * @since   2.1
     */
    protected function doRequest(RequestInterface $request, array $options = []): ResponseInterface
    {
        $stream = $this->createStream($request, $options);

        if ($dest = ($options['target_file'] ?? null)) {
            $content = '';
            StreamHelper::copyTo($stream, $dest);
        } else {
            $content = $stream->getContents();
        }

        $metadata = $stream->getMetadata();

        $stream->close();

        if (isset($metadata['wrapper_data']['headers'])) {
            $headers = $metadata['wrapper_data']['headers'];
        } elseif (isset($metadata['wrapper_data'])) {
            $headers = $metadata['wrapper_data'];
        } else {
            $headers = [];
        }

        return $this->toResponse($headers, $content);
    }

    /**
     * @throws \Exception
     */
    public function createConnection(RequestInterface $request, array $options = []): mixed
    {
        // Create the stream context options array with the required method offset.
        $opt = ['method' => $request->getMethod()];

        // Set HTTP Version
        $opt['protocol_version'] = $request->getProtocolVersion();

        // Handle data
        $body = $request->getBody();
        $forceMultipart = false;

        if ($body instanceof RequestBodyStream) {
            $data = $body->getData();

            Arr::mapRecursive(
                $data,
                static function ($value) use (&$forceMultipart) {
                    if ($value instanceof HttpUploadFileInterface) {
                        $forceMultipart = true;
                    }

                    return $value;
                }
            );
        } else {
            $data = (string) $body;
        }

        $contentType = $request->getHeaderLine('Content-Type');

        if ($forceMultipart || str_starts_with($contentType, HttpClientInterface::MULTIPART_FORMDATA)) {
            if (is_array($data)) {
                $boundary = MultipartHelper::createBoundary();

                $data = MultipartHelper::toFormData($boundary, $data);

                $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . $boundary);
            }

            $opt['content'] = $data;
        } else {
            if (is_scalar($data)) {
                // If the data is a scalar value simply add it to the stream context options.
                $opt['content'] = $data;
            } else {
                // Otherwise we need to encode the value first.
                $opt['content'] = http_build_query($data);
            }

            if (!$request->getHeaderLine('Content-Type')) {
                $request = $request->withHeader(
                    'Content-Type',
                    'application/x-www-form-urlencoded; charset=utf-8'
                );
            }
        }

        if (!$request->getHeader('Content-Type')) {
            $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
        }

        // Add the relevant headers.
        $request = $request->withHeader('Content-Length', (string) strlen($opt['content']));

        // Speed up stream get URL
        // @see http://stackoverflow.com/questions/3629504/php-file-get-contents-very-slow-when-using-full-url
        // @see http://stackoverflow.com/questions/13679976/how-to-speed-up-file-get-contents
        // $request = $request->withHeader('Connection', 'Close');

        // Build the headers string for the request.
        if ($headers = $request->getHeaders()) {
            // Add the headers string into the stream context options array.
            $opt['header'] = HeaderHelper::toHeaderLines($headers, true);
        }

        // If an explicit timeout is given user it.
        if ($this->getOption('timeout')) {
            $opt['timeout'] = (int) $this->getOption('timeout');
        }

        // If an explicit user agent is given use it.
        if ($this->getOption('userAgent')) {
            $opt['user_agent'] = $this->getOption('userAgent');
        }

        // Ignore HTTP errors so that we can capture them.
        $opt['ignore_errors'] = 1;

        // Follow redirects.
        $opt['follow_location'] = (int) $this->getOption('follow_location', 1);

        $opt = array_merge($opt, $options);

        // Create the stream context for the request.
        $context = stream_context_create(['http' => $opt]);

        // Capture PHP errors
        return @fopen($request->getRequestTarget(), READ_ONLY_FROM_BEGIN, false, $context);
    }

    /**
     * createStream
     *
     * @param  RequestInterface  $request
     * @param  array             $options
     *
     * @return  Stream
     */
    protected function createStream(RequestInterface $request, array $options): Stream
    {
        $connection = $this->createConnection($request, $options);

        if (!$connection) {
            $error = error_get_last();

            throw new HttpRequestException($error['message'] ?? 'Unknown error');
        }

        return new Stream($connection);
    }

    /**
     * Method to get a response object from a server response.
     *
     * @param  array                   $headers  The response headers as an array.
     * @param  string                  $body     The response body as a string.
     * @param  ResponseInterface|null  $response
     *
     * @return ResponseInterface
     *
     * @psalm-template R
     * @psalm-param R $response
     * @psalm-return R
     *
     * @since    2.1
     */
    public function toResponse(array $headers, string $body, ?ResponseInterface $response = null): ResponseInterface
    {
        $response ??= new Response();

        // Set the body for the response.
        $response->getBody()->write($body);

        $response->getBody()->rewind();

        // Get the response code from the first offset of the response headers.
        preg_match('/[0-9]{3}/', array_shift($headers), $matches);
        $code = $matches[0];

        if (is_numeric($code)) {
            $response = $response->withStatus($code);
        } elseif (!$this->getOption('allow_empty_status_code', false)) {
            // No valid response code was detected.
            throw new UnexpectedValueException('No HTTP response code found.');
        }
        // Add the response headers to the response object.
        foreach ($headers as $header) {
            $pos = strpos($header, ':');

            $response = $response->withHeader(trim(substr($header, 0, $pos)), trim(substr($header, ($pos + 1))));
        }

        return $response;
    }

    /**
     * Use stream to download file.
     *
     * @param  RequestInterface        $request  The request object to store request params.
     * @param  string|StreamInterface  $dest     The dest path to store file.
     *
     * @param  array                   $options
     *
     * @return  ResponseInterface
     * @since   2.1
     */
    public function download(
        RequestInterface $request,
        string|StreamInterface $dest,
        array $options = []
    ): ResponseInterface {
        if (!$dest) {
            throw new InvalidArgumentException('Target file path is emptty.');
        }

        if (!$dest instanceof StreamInterface) {
            $dest = Stream::fromFilePath($dest);
        }

        $options['target_file'] = $dest;

        $response = $this->request($request, $options);

        $dest->close();

        return $response;
    }

    /**
     * Method to check if HTTP transport layer available for using
     *
     * @return  bool  True if available else false
     *
     * @since   2.0
     */
    public static function isSupported(): bool
    {
        return function_exists('fopen') && is_callable('fopen') && ini_get('allow_url_fopen');
    }
}
