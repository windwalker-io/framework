<?php

declare(strict_types=1);

namespace Windwalker\Http\Factory;

use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use UnexpectedValueException;
use Windwalker\Http\Helper\MultipartParser;
use Windwalker\Http\HttpParameters;
use Windwalker\Http\Request\RequestBaseUri;
use Windwalker\Http\Request\ServerRequest;
use Windwalker\Http\SafeJson;
use Windwalker\Http\UploadedFile;
use Windwalker\Stream\PhpInputStream;
use Windwalker\Uri\Uri;

/**
 * The ServerRequestFactory class.
 *
 * @since  3.0
 */
class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * Function name to get apache request headers. This property is for test use.
     *
     * @var callable
     */
    public static $apacheRequestHeaders = 'apache_request_headers';

    /**
     * @inheritDoc
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest(
            $serverParams,
            [],
            $uri,
            $method
        );
    }

    /**
     * Create a request from the supplied superglobal values.
     *
     * If any argument is not supplied, the corresponding superglobal value will
     * be used.
     *
     * @param  array|HttpParameters $server     The $_SERVER superglobal variable.
     * @param  array                $query      The $_GET superglobal variable.
     * @param array|null            $parsedBody The $_POST superglobal variable.
     * @param  array                $cookies    The $_COOKIE superglobal variable.
     * @param  array                $files      The $_FILES superglobal variable.
     *
     * @return ServerRequestInterface
     *
     * @throws JsonException
     */
    public static function createFromGlobals(
        array|HttpParameters $server = [],
        array $query = [],
        ?array $parsedBody = null,
        array $cookies = [],
        array $files = []
    ): ServerRequestInterface {
        $server = HttpParameters::wrap($server ?: $_SERVER);
        $server = static::prepareServers($server);
        $headers = static::prepareHeaders($server);

        $body = PhpInputStream::getInstance();

        $method = $server['REQUEST_METHOD'] ?? 'GET';

        $decodedBody = $_POST;
        $decodedFiles = $_FILES;
        $method = strtoupper($method);
        $type = (string) $headers['Content-Type'];

        if ($method === 'POST') {
            if (str_contains($type, 'application/json')) {
                $decodedBody = new SafeJson($body->__toString(), true, 512, JSON_THROW_ON_ERROR);
            }
        } elseif (in_array($method, ['PUT', 'PATCH', 'DELETE', 'LINK', 'UNLINK'])) {
            if (str_contains($type, 'application/x-www-form-urlencoded')) {
                parse_str($body->__toString(), $decodedBody);
            } elseif (str_contains($type, 'multipart/form-data')) {
                [$decodedBody, $decodedFiles] = array_values(MultipartParser::parseFormData($body->__toString()));
            } elseif (str_contains($type, 'application/json')) {
                $decodedBody = new SafeJson($body->__toString(), true, 512, JSON_THROW_ON_ERROR);
            }
        }

        $files = static::prepareFiles($files ?: $decodedFiles);

        return new ServerRequest(
            array_change_key_case($server->dump(), CASE_UPPER),
            $files,
            static::prepareUri($server, $headers),
            $method,
            $body,
            $headers->dump(),
            $cookies ?: $_COOKIE,
            $query ?: $_GET,
            $parsedBody ?: $decodedBody,
            static::getProtocolVersion($server)
        );
    }

    /**
     * createFromUri
     *
     * @param  string|UriInterface   $uri
     * @param  string|null           $script
     * @param  array|HttpParameters  $server
     * @param  array                 $query
     * @param  array|null            $parsedBody
     * @param  array                 $cookies
     * @param  array                 $files
     *
     * @return  ServerRequestInterface
     * @throws JsonException
     */
    public static function createFromUri(
        string|UriInterface $uri,
        ?string $script = null,
        array|HttpParameters $server = [],
        array $query = [],
        ?array $parsedBody = null,
        array $cookies = [],
        array $files = []
    ): ServerRequestInterface {
        if (is_string($uri)) {
            $uri = new Uri($uri);
        }

        $server = HttpParameters::wrap($server ?: $_SERVER);

        if ($script) {
            $server['SCRIPT_NAME'] = $script;
        }

        $server['SCRIPT_NAME'] = '/' . ltrim($server['SCRIPT_NAME'], '/');

        $request = static::createFromGlobals($server, $query, $parsedBody, $cookies, $files);

        return $request->withUri($uri);
    }

    /**
     * Prepare the $_SERVER variables.
     *
     * @param  array|HttpParameters  $server  The $_SERVER superglobal variable.
     *
     * @return HttpParameters
     */
    public static function prepareServers(array|HttpParameters $server): HttpParameters
    {
        $server = HttpParameters::wrap($server);

        // Authorization can only get by apache_request_headers()
        $apacheRequestHeaders = static::$apacheRequestHeaders;

        $httpAuth = $server->get('HTTP_AUTHORIZATION');

        if (isset($httpAuth) || !is_callable($apacheRequestHeaders)) {
            return $server;
        }

        $apacheRequestHeaders = array_change_key_case($apacheRequestHeaders(), CASE_LOWER);

        if (isset($apacheRequestHeaders['authorization'])) {
            $server->set('HTTP_AUTHORIZATION', $apacheRequestHeaders['authorization']);

            return $server;
        }

        return $server;
    }

    /**
     * Normalize uploaded files
     *
     * Transforms each value into an UploadedFileInterface instance, and ensures
     * that nested arrays are normalized.
     *
     * @param  array  $files  THe $_FILES superglobal variable.
     *
     * @return  UploadedFileInterface[]
     *
     * @throws  InvalidArgumentException for unrecognized values
     */
    public static function prepareFiles(array $files): array
    {
        $return = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $return[$key] = $value;

                continue;
            }

            // Nested files
            if (is_array($value) && isset($value['tmp_name'])) {
                $return[$key] = static::createUploadedFile($value);

                continue;
            }

            // get next level files
            if (is_array($value)) {
                $return[$key] = static::prepareFiles($value);

                continue;
            }

            throw new InvalidArgumentException('Invalid value in files specification');
        }

        return $return;
    }

    /**
     * Get headers from $_SERVER.
     *
     * @param  array|HttpParameters  $server  The $_SERVER superglobal variable.
     *
     * @return HttpParameters
     */
    public static function prepareHeaders(array|HttpParameters $server): HttpParameters
    {
        $server = HttpParameters::wrap($server);

        $headers = new HttpParameters();

        foreach ($server as $key => $value) {
            $key = strtolower($key);

            if ($value && str_starts_with($key, 'http_')) {
                $name = str_replace('_', ' ', substr($key, 5));
                $name = str_replace(' ', '-', ucwords(strtolower($name)));
                $name = strtolower($name);

                $headers[$name] = $value;

                continue;
            }

            if ($value && str_starts_with($key, 'content_')) {
                $name = substr($key, 8);
                $name = 'content-' . strtolower($name);

                $headers[$name] = $value;

                continue;
            }
        }

        return $headers;
    }

    /**
     * Marshal the URI from the $_SERVER array and headers
     *
     * @param  array|HttpParameters       $server   The $_SERVER superglobal.
     * @param  array|HttpParameters|null  $headers  The headers variable from server.
     *
     * @return  UriInterface  Prepared Uri object.
     */
    public static function prepareUri(
        array|HttpParameters $server,
        array|HttpParameters|null $headers = null
    ): UriInterface {
        $server = HttpParameters::wrap($server);

        $headers ??= static::prepareHeaders($server);

        $headers = HttpParameters::wrap($headers);

        $uri = new Uri('');

        // URI scheme
        $scheme = 'http';
        $https = $server['HTTPS'];

        // Is https or not
        if (($https && $https !== 'off') || ($headers['x-forwarded-proto'] ?? false) === 'https') {
            $scheme = 'https';
        }

        // User Info
        $user = $server['PHP_AUTH_USER'] ?? '';
        $pass = $server['PHP_AUTH_PW'] ?? null;

        // URI host
        $host = '';
        $port = null;

        static::getHostAndPortFromHeaders($host, $port, $server, $headers);

        // URI path
        $path = static::getRequestUri($server);
        $path = static::stripQueryString($path);

        // URI query
        $query = '';

        if (isset($server['QUERY_STRING'])) {
            $query = ltrim($server['QUERY_STRING'], '?');
        }

        // URI fragment
        $fragment = '';

        if (str_contains($path, '#')) {
            [$path, $fragment] = explode('#', $path, 2);
        }

        return $uri->withScheme($scheme)
            ->withUserInfo($user, $pass)
            ->withHost($host)
            ->withPort($port)
            ->withPath($path)
            ->withFragment($fragment)
            ->withQuery($query);
    }

    public static function createBaseUriFromRequest(ServerRequestInterface $request): RequestBaseUri
    {
        return RequestBaseUri::parseFromRequest($request);
    }

    /**
     * Marshal the host and port from HTTP headers and/or the PHP environment
     *
     * @param  string                $host     The uri host.
     * @param  ?int                  $port     The request port.
     * @param  array|HttpParameters  $server   The $_SERVER superglobal.
     * @param  array|HttpParameters  $headers  The headers variable from server.
     */
    public static function getHostAndPortFromHeaders(
        string &$host,
        ?int &$port,
        array|HttpParameters $server,
        array|HttpParameters $headers
    ): void {
        $server = HttpParameters::wrap($server);
        $headers = HttpParameters::wrap($headers);

        if ($headers['host'] ?? false) {
            static::getHostAndPortFromHeader($host, $port, $headers['host']);

            return;
        }

        $serverName = $server['SERVER_NAME'];

        if (!isset($serverName)) {
            return;
        }

        $host = $serverName;
        $port = $server['SERVER_PORT'];

        if (isset($port)) {
            $port = (int) $port;
        }

        $addr = $server['SERVER_ADDR'];

        if (!isset($addr) || !preg_match('/^\[[0-9a-fA-F\:]+\]$/', $host)) {
            return;
        }

        // Handle Ipv6
        $host = '[' . $addr . ']';
        $port = $port ?: 80;

        if ($port . ']' === substr($host, strrpos($host, ':') + 1)) {
            // The last digit of the IPv6-Address has been taken as port
            // Unset the port so the default port can be used
            $port = null;
        }
    }

    /**
     * Get the base URI for the $_SERVER superglobal.
     *
     * Try to auto detect the base URI from different server system including IIS and Apache.
     *
     * This method based on ZF2's Zend\Http\PhpEnvironment\Request class
     *
     * @see       https://github.com/zendframework/zend-http/blob/master/src/PhpEnvironment/Request.php
     *
     * @copyright Copyright (C) 2023-2015 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     *
     * @param  array|HttpParameters  $server
     *
     * @return string
     */
    public static function getRequestUri(array|HttpParameters $server): string
    {
        $server = HttpParameters::wrap($server);

        // IIS7 with URL Rewrite: make sure we get the unencoded url
        // (double slash problem).
        $iisUrlRewritten = $server['IIS_WasUrlRewritten'];
        $unencodedUrl = $server['UNENCODED_URL'] ?? '';

        if ('1' === (string) $iisUrlRewritten && !empty($unencodedUrl)) {
            return $unencodedUrl;
        }

        $requestUri = $server['REQUEST_URI'];

        // Check this first so IIS will catch.
        $httpXRewriteUrl = $server['HTTP_X_REWRITE_URL'];

        if ($httpXRewriteUrl !== null) {
            $requestUri = $httpXRewriteUrl;
        }

        // Check for IIS 7.0 or later with ISAPI_Rewrite
        $httpXOriginalUrl = $server['HTTP_X_ORIGINAL_URL'];

        if ($httpXOriginalUrl !== null) {
            $requestUri = $httpXOriginalUrl;
        }

        if ($requestUri !== null) {
            return preg_replace('#^[^/:]+://[^/]+#', '', $requestUri);
        }

        $origPathInfo = $server['ORIG_PATH_INFO'];

        if (empty($origPathInfo)) {
            return '/';
        }

        return $origPathInfo;
    }

    /**
     * Strip the query string from a path
     *
     * @param  string  $path  The uri path.
     *
     * @return  string  The path striped.
     */
    public static function stripQueryString(string $path): string
    {
        $qMark = strpos($path, '?');

        if ($qMark !== false) {
            return substr($path, 0, $qMark);
        }

        return $path;
    }

    /**
     * Marshal the host and port from the request header
     *
     * @param  string        $host
     * @param  int|null      $port
     * @param  string|array  $headerHost
     */
    protected static function getHostAndPortFromHeader(string &$host, ?int &$port, string|array $headerHost): void
    {
        if (is_array($headerHost)) {
            $headerHost = implode(', ', $headerHost);
        }

        $host = $headerHost;

        if (preg_match('|\:(\d+)$|', $host, $matches)) {
            $host = substr($host, 0, -1 * (strlen($matches[1]) + 1));
            $port = (int) $matches[1];
        }
    }

    /**
     * Create an UploadedFile object for every uploaded file specifications.
     *
     * If an element is array, will call getFlattenFileData() to normalize them to
     * a standard nested file list.
     *
     * @param  array  $value  $_FILES  struct.
     *
     * @return  UploadedFileInterface|UploadedFileInterface[]
     */
    private static function createUploadedFile(array $value): array|UploadedFileInterface
    {
        // Flatten file if is nested.
        if (is_array($value['tmp_name'])) {
            return static::getFlattenFileData($value);
        }

        $file = new UploadedFile(
            $value['tmp_name'],
            $value['size'],
            $value['error'],
            $value['name'],
            $value['type']
        );

        if (!empty($value['full_path']) && is_string($value['full_path'])) {
            $file->setFullPath($value['full_path']);
        }

        return $file;
    }

    /**
     * Normalize an array of file specifications.
     *
     * Loops through all nested files and returns a normalized array of
     * UploadedFileInterface instances.
     *
     * @param  array  $files  The file spec array.
     *
     * @return  UploadedFileInterface[]
     */
    protected static function getFlattenFileData(array $files = []): array
    {
        $return = [];

        foreach (array_keys($files['tmp_name']) as $key) {
            $file = [
                'tmp_name' => $files['tmp_name'][$key],
                'size' => $files['size'][$key],
                'error' => $files['error'][$key],
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'full_path' => $files['full_path'][$key] ?? null,
            ];

            $return[$key] = self::createUploadedFile($file);
        }

        return $return;
    }

    /**
     * Return HTTP protocol version (X.Y)
     *
     * @param  array|HttpParameters  $server  The $_SERVER supperglobal.
     *
     * @return  string  Protocol version.
     */
    public static function getProtocolVersion(array|HttpParameters $server): string
    {
        $server = HttpParameters::wrap($server);

        if (!isset($server['SERVER_PROTOCOL'])) {
            return '1.1';
        }

        if (!preg_match('/^(HTTP\/)?(\d+(?:\.\d+)+)/', $server['SERVER_PROTOCOL'], $matches)) {
            throw new UnexpectedValueException(
                sprintf(
                    'Invalid protocol version format (%s)',
                    $server['SERVER_PROTOCOL']
                )
            );
        }

        return $matches[2];
    }
}
