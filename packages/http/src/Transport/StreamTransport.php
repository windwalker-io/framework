<?php

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
use Windwalker\Http\Helper\MultipartParser;
use Windwalker\Http\HttpClientInterface;
use Windwalker\Http\Response\HttpClientResponse;
use Windwalker\Http\Stream\RequestBodyStream;
use Windwalker\Http\Transport\Options\StreamOptions;
use Windwalker\Http\Transport\Options\TransportOptions;
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
     * @param  RequestInterface        $request  The request object to store request params.
     *
     * @param  array|TransportOptions  $options
     *
     * @return  HttpClientResponse
     *
     * @throws \Exception
     * @since   2.1
     */
    protected function doRequest(RequestInterface $request, array|TransportOptions $options = []): HttpClientResponse
    {
        $options = $this->mergeOptions($options);

        $stream = $this->createStream($request, $options);

        $dest = $options->writeStream ?? $options->targetFile;

        if ($dest) {
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

        return $this->toResponse(
            $headers,
            $content,
            new HttpClientResponse()->withInfo($metadata),
            $options->allowEmptyStatusCode
        );
    }

    /**
     * @param  RequestInterface     $request
     * @param  array|StreamOptions  $options
     *
     * @return  resource|false
     *
     * @throws \Exception
     */
    public function createConnection(RequestInterface $request, array|StreamOptions $options = []): mixed
    {
        $options = $this->mergeOptions($options);

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
                $boundary = MultipartParser::createBoundary();

                $data = MultipartParser::toFormData($boundary, $data);

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
        if ($timeout = $options->timeout) {
            $opt['timeout'] = (int) $timeout;
        }

        // If an explicit user agent is given use it.
        if ($userAgent = $options->userAgent) {
            $opt['user_agent'] = $userAgent;
        }

        // Ignore HTTP errors so that we can capture them.
        $opt['ignore_errors'] = 1;

        // Follow redirects.
        $opt['follow_location'] = (int) ($options->followLocation ?? true);

        $opt['ssl']['verify_peer'] = (int) $options->verifyPeer;

        $opt = $this->setCABundleToOptions($opt, $options);

        // Create the stream context for the request.
        $context = stream_context_create(
            Arr::mergeRecursive(
                [
                    'http' => $opt
                ],
                $options->context
            )
        );

        // Capture PHP errors
        return @fopen($request->getRequestTarget(), READ_ONLY_FROM_BEGIN, false, $context);
    }

    /**
     * createStream
     *
     * @param  RequestInterface  $request
     * @param  StreamOptions     $options
     *
     * @return  Stream
     * @throws \Exception
     */
    protected function createStream(RequestInterface $request, StreamOptions $options): Stream
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
     * @psalm-param R                  $response
     * @psalm-return R
     *
     * @since          2.1
     */
    public function toResponse(
        array $headers,
        string $body,
        ?ResponseInterface $response = null,
        bool $allowEmptyStatusCode = false
    ): ResponseInterface {
        $response ??= new HttpClientResponse();

        // Set the body for the response.
        $response->getBody()->write($body);

        $response->getBody()->rewind();

        // Get the response code from the first offset of the response headers.
        preg_match('/[0-9]{3}/', array_shift($headers), $matches);
        $code = $matches[0];

        if (is_numeric($code)) {
            $response = $response->withStatus($code);
        } elseif (!$allowEmptyStatusCode) {
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
     * @param  array|TransportOptions  $options
     *
     * @return  HttpClientResponse
     * @since   2.1
     */
    public function download(
        RequestInterface $request,
        string|StreamInterface $dest,
        array|TransportOptions $options = []
    ): HttpClientResponse {
        if (!$dest) {
            throw new InvalidArgumentException('Target file path is emptty.');
        }

        $options = $this->mergeOptions($options);

        if (!$dest instanceof StreamInterface) {
            $dest = Stream::fromFilePath($dest);
        }

        $options->writeStream = $dest;

        return $this->request($request, $options);
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

    protected function setCABundleToOptions(array $context, StreamOptions $options): array
    {
        if ($options->certpath) {
            $context['ssl']['capath'] = $options->context;

            return $context;
        }

        $caPathOrFile = $this->findCAPathOrFile();

        if (is_dir($caPathOrFile) || (is_link($caPathOrFile) && is_dir(readlink($caPathOrFile)))) {
            $context['ssl']['cafile'] = $caPathOrFile;
        } else {
            $context['ssl']['capath'] = $caPathOrFile;
        }

        return $context;
    }

    public function mergeOptions(array|TransportOptions $options): StreamOptions
    {
        return StreamOptions::wrap($options)->withDefaults($this->options, true);
    }
}
