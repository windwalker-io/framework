<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Transport;

use Composer\CaBundle\CaBundle;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use UnexpectedValueException;
use Windwalker\Http\Exception\HttpRequestException;
use Windwalker\Http\File\HttpUploadFileInterface;
use Windwalker\Http\Helper\HeaderHelper;
use Windwalker\Http\HttpClientInterface;
use Windwalker\Http\Response\HttpClientResponse;
use Windwalker\Http\Response\Response;
use Windwalker\Http\Stream\RequestBodyStream;
use Windwalker\Stream\Stream;
use Windwalker\Utilities\Arr;

use const Windwalker\Stream\READ_WRITE_RESET;

/**
 * The CurlTransport class.
 *
 * @since  2.1
 */
class CurlTransport extends AbstractTransport implements CurlTransportInterface
{
    /**
     * Send a request to the server and return a Response object with the response.
     *
     * @param  RequestInterface  $request  The request object to store request params.
     *
     * @param  array             $options
     *
     * @return  HttpClientResponse
     *
     * @since    2.1
     */
    protected function doRequest(RequestInterface $request, array $options = []): HttpClientResponse
    {
        $options = Arr::mergeRecursive(
            [
                'ignore_curl_error' => false,
                'write_stream' => 'php://memory',
            ],
            $this->getOptions(),
            $options
        );

        $ch = $this->createHandle($request, $options);

        $headerGroup = [];
        $content = Stream::wrap($options['write_stream'], READ_WRITE_RESET);
        $i = 0;

        curl_setopt(
            $ch,
            CURLOPT_HEADERFUNCTION,
            static function (\CurlHandle $ch, string $header) use (&$headerGroup, &$i) {
                if ($header === "\r\n") {
                    $i++;
                } else {
                    $headerGroup[$i][] = $header;
                }

                return strlen($header);
            }
        );

        curl_setopt(
            $ch,
            CURLOPT_WRITEFUNCTION,
            static function ($ch, $str) use ($content) {
                $content->write($str);

                return strlen($str);
            }
        );

        // Execute the request and close the connection.
        $c = curl_exec($ch);

        $error = curl_error($ch);

        if ($error !== '' && !$options['ignore_curl_error']) {
            throw new HttpRequestException($error, curl_errno($ch));
        }

        // Get the request information.
        $info = (array) curl_getinfo($ch);

        // Close the connection.
        curl_close($ch);

        $content->rewind();

        return $this->injectHeadersToResponse(
            (new HttpClientResponse($content))->withInfo($info),
            array_pop($headerGroup),
        );
    }

    /**
     * Method to get a response object from a server response.
     *
     * @param  string  $content   The complete server response, including headers
     *                            as a string if the response has no errors.
     * @param  array   $info      The cURL request information.
     *
     * @return  Response|ResponseInterface
     *
     * @throws  UnexpectedValueException
     * @since       2.0
     *
     * @deprecated  Use toResponse()
     */
    public function getResponse(string $content, array $info): Response|ResponseInterface
    {
        // Create the response object.
        $return = $this->createResponse();

        return $this->contentToResponse($content, $info, $return);
    }

    /**
     * @template R
     *
     * @param  R      $response
     * @param  array  $headers
     *
     * @return  R
     */
    public function injectHeadersToResponse(ResponseInterface $response, array $headers): ResponseInterface
    {
        // Get the response code from the first offset of the response headers.
        preg_match('/[0-9]{3}/', array_shift($headers), $matches);

        $code = count($matches) ? $matches[0] : null;

        if (is_numeric($code)) {
            $response = $response->withStatus($code);
        } elseif (!$this->getOption('allow_empty_status_code', false)) {
            // No valid response code was detected.
            throw new HttpRequestException('No HTTP response code found.');
        }

        // Add the response headers to the response object.
        foreach ($headers as $header) {
            $pos = strpos($header, ':');

            $response = $response->withHeader(trim(substr($header, 0, $pos)), trim(substr($header, ($pos + 1))));
        }

        return $response;
    }

    public function contentToResponse(
        string $content,
        array $info = [],
        ?ResponseInterface $response = null
    ): ResponseInterface {
        $response ??= $this->createResponse();

        // Get the number of redirects that occurred.
        $redirects = $info['redirect_count'] ?? 0;

        /*
         * Split the response into headers and body. If cURL encountered redirects,
         * the headers for the redirected requests will
         * also be included. So we split the response into header + body + the number of redirects
         * and only use the last two sections which should be the last set of headers and the actual body.
         */
        $parts = explode("\r\n\r\n", $content, 2 + $redirects);

        // Set the body for the response.
        $response->getBody()->write(array_pop($parts));

        $response->getBody()->rewind();

        // Get the last set of response headers as an array.
        $headers = explode("\r\n", (string) array_pop($parts));

        return $this->injectHeadersToResponse($response, $headers);
    }

    /**
     * @param  mixed  $body
     *
     * @return  ResponseInterface
     *
     * @since  3.5.19
     */
    protected function createResponse(mixed $body = 'php://memory'): ResponseInterface
    {
        $class = $this->getOption('response_class') ?? Response::class;

        return new $class($body);
    }

    /**
     * createHandle
     *
     * @param  RequestInterface  $request
     * @param  array             $options
     *
     * @return  \CurlHandle|false
     *
     * @since  3.2
     */
    public function createHandle(RequestInterface $request, array $options): \CurlHandle|false
    {
        // Setup the cURL handle.
        $ch = curl_init();

        $opt = $this->prepareCurlOptions($request, $options);

        curl_setopt_array($ch, $opt);

        return $ch;
    }

    /**
     * setTheRequestMethod
     *
     * @param  RequestInterface  $request
     * @param  array             $options
     *
     * @return  array
     */
    protected function prepareCurlOptions(RequestInterface $request, array $options): array
    {
        // Set the request method.
        $opt[CURLOPT_CUSTOMREQUEST] = $request->getMethod();

        // Don't wait for body when $method is HEAD
        $opt[CURLOPT_NOBODY] = ($request->getMethod() === 'HEAD');

        // Initialize the certificate store
        $opt = $this->setCABundleToOptions($opt);

        // Set HTTP Version
        switch ($request->getProtocolVersion()) {
            case '1.0':
                $opt[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_0;
                break;

            case '1.1':
                $opt[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
                break;

            case '2':
                if (defined('CURL_HTTP_VERSION_2_0')) {
                    $opt[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_2_0;
                }
        }

        // Handle data
        $request = static::prepareBody($request, $forceMultipart);
        $data = (string) $request->getBody();

        if ($data !== '') {
            $opt[CURLOPT_POSTFIELDS] = $data;
        }

        $request = static::prepareHeaders($request, $forceMultipart);

        // Add the relevant headers.
        if (isset($opt[CURLOPT_POSTFIELDS]) && $opt[CURLOPT_POSTFIELDS] !== '') {
            $request = $request->withHeader('Content-Length', (string) strlen($opt[CURLOPT_POSTFIELDS]));
        }

        // Build the headers string for the request.
        if ($headers = $request->getHeaders()) {
            // Add the headers string into the stream context options array.
            $opt[CURLOPT_HTTPHEADER] = HeaderHelper::toHeaderLines($headers);
        }

        // If an explicit timeout is given user it.
        if ($timeout = $this->getOption('timeout')) {
            $opt[CURLOPT_TIMEOUT] = (int) $timeout;
            $opt[CURLOPT_CONNECTTIMEOUT] = (int) $timeout;
        }

        // If an explicit user agent is given use it.
        if ($userAgent = $this->getOption('userAgent')) {
            $opt[CURLOPT_USERAGENT] = $userAgent;
        }

        // Set the request URL.
        $opt[CURLOPT_URL] = (string) $request->getRequestTarget();

        // We want our headers. :-)
        // $opt[CURLOPT_HEADER] = true;

        // Return it... echoing it would be tacky.
        $opt[CURLOPT_RETURNTRANSFER] = true;

        // Override the Expect header to prevent cURL from confusing itself in its own stupidity.
        // Link: http://the-stickman.com/web-development/php-and-curl-disabling-100-continue-header/
        $opt[CURLOPT_HTTPHEADER][] = 'Expect:';

        /*
         * Follow redirects if server config allows
         */
        if (!ini_get('open_basedir')) {
            $opt[CURLOPT_FOLLOWLOCATION] = $this->getOption('follow_location') ?? true;
        }

        // Set any custom transport options
        $opt += $options['curl'] ?? $options['options'] ?? [];

        return $opt;
    }

    /**
     * @param  RequestInterface  $request
     * @param  bool              $forceMultipart
     *
     * @return  RequestInterface
     */
    public static function prepareBody(RequestInterface $request, bool &$forceMultipart = null): RequestInterface
    {
        $body = $request->getBody();

        $forceMultipart ??= false;

        if ($body instanceof RequestBodyStream) {
            $data = $body->getData();

            $data = Arr::mapRecursive($data, static function ($value) use (&$forceMultipart) {
                if ($value instanceof HttpUploadFileInterface) {
                    $value = $value->toCurlFile();
                    $forceMultipart = true;
                }

                return $value;
            });

            $body->setData($data);
        }

        return $request->withBody($body);
    }

    /**
     * @param  RequestInterface  $request
     * @param  bool              $forceMultipart
     *
     * @return  RequestInterface
     */
    public static function prepareHeaders(RequestInterface $request, bool $forceMultipart = false): RequestInterface
    {
        $contentType = $request->getHeaderLine('Content-Type');

        $postMethods = [
            'post',
            'put',
            'patch',
            'delete',
        ];

        if (in_array(strtolower((string) $request->getMethod()), $postMethods, true)) {
            if ($forceMultipart || str_starts_with($contentType, HttpClientInterface::MULTIPART_FORMDATA)) {
                // If no boundary, remove content-type and let CURL add it.
                if (!str_contains($contentType, 'boundary')) {
                    $request = $request->withoutHeader('Content-Type');
                }
            } elseif (!$request->hasHeader('Content-Type')) {
                $request = $request->withHeader(
                    'Content-Type',
                    'application/x-www-form-urlencoded; charset=utf-8'
                );
            }
        }

        return $request;
    }

    /**
     * Use stream to download file.
     *
     * @param  RequestInterface        $request  The request object to store request params.
     * @param  string|StreamInterface  $dest     The dest path to store file.
     *
     * @param  array                   $options
     *
     * @return  HttpClientResponse
     * @since   2.1
     */
    public function download(
        RequestInterface $request,
        string|StreamInterface $dest,
        array $options = []
    ): HttpClientResponse {
        if (!$dest) {
            throw new InvalidArgumentException('Target file path is empty.');
        }

        $options['write_stream'] = $dest;

        return $this->request(
            $request,
            $options
        );
    }

    /**
     * Method to check if HTTP transport layer available for using
     *
     * @return  bool  True if available else false
     *
     * @since   2.1
     */
    public static function isSupported(): bool
    {
        return function_exists('curl_init') && is_callable('curl_init');
    }

    /**
     * setCABundleToOptions
     *
     * @param  array  $options
     *
     * @return  array
     *
     * @since  3.4.2
     */
    protected function setCABundleToOptions(array $options): array
    {
        if ($this->getOption('certpath')) {
            $options[CURLOPT_CAINFO] = $this->getOption('certpath');

            return $options;
        }

        $caPathOrFile = CaBundle::getBundledCaBundlePath();

        if (is_dir($caPathOrFile) || (is_link($caPathOrFile) && is_dir(readlink($caPathOrFile)))) {
            $options[CURLOPT_CAPATH] = $caPathOrFile;
        } else {
            $options[CURLOPT_CAINFO] = $caPathOrFile;
        }

        return $options;
    }
}
