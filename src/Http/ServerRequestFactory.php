<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http;

use Psr\Http\Message\UploadedFileInterface;
use Windwalker\Http\Helper\HeaderHelper;
use Windwalker\Http\Helper\ServerHelper;
use Windwalker\Uri\PsrUri;

/**
 * The ServerRequestFactory class.
 *
 * @since  {DEPLOY_VERSION}
 */
class ServerRequestFactory
{
	/**
	 * Function name to get apache request headers. This property is for test.
	 *
	 * @var callable
	 */
	public static $apacheRequestHeaders = 'apache_request_headers';

	/**
	 * Create a request from the supplied superglobal values.
	 *
	 * If any argument is not supplied, the corresponding superglobal value will
	 * be used.
	 *
	 * The ServerRequest created is then passed to the fromServer() method in
	 * order to marshal the request URI and headers.
	 *
	 * @see fromServer()
	 *
	 * @param array $server  $_SERVER superglobal
	 * @param array $query   $_GET superglobal
	 * @param array $body    $_POST superglobal
	 * @param array $cookies $_COOKIE superglobal
	 * @param array $files   $_FILES superglobal
	 *
	 * @return ServerRequest
	 * @throws \InvalidArgumentException for invalid file values
	 */
	public static function fromGlobals(array $server = array(), array $query = array(), array $body = null,
		array $cookies = array(), array $files = array())
	{
		$server  = static::prepareServers($server ? : $_SERVER);
		$files   = static::prepareFiles($files ? : $_FILES);
		$headers = static::prepareHeaders($server);

		return new ServerRequest(
			$server,
			$files,
			static::prepareUri($server, $headers),
			ServerHelper::getValue($server, 'REQUEST_METHOD', 'GET'),
			'php://input',
			$headers,
			$cookies ? : $_COOKIE,
			$query   ? : $_GET,
			$body    ? : $_POST,
			static::getProtocolVersion($server)
		);

	}

	/**
	 * Marshal the $_SERVER array
	 *
	 * Pre-processes and returns the $_SERVER superglobal.
	 *
	 * @param array $server
	 *
	 * @return array
	 */
	public static function prepareServers(array $server)
	{
		// Authorization can only get by apache_request_headers()
		$apacheRequestHeaders = static::$apacheRequestHeaders;

		if (isset($server['HTTP_AUTHORIZATION']) || !is_callable($apacheRequestHeaders))
		{
			return $server;
		}

		$apacheRequestHeaders = array_change_key_case($apacheRequestHeaders(), CASE_LOWER);

		if (isset($apacheRequestHeaders['authorization']))
		{
			$server['HTTP_AUTHORIZATION'] = $apacheRequestHeaders['authorization'];

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
	 * @param array $files
	 *
	 * @return UploadedFileInterface[]
	 * @throws \InvalidArgumentException for unrecognized values
	 */
	public static function prepareFiles(array $files)
	{
		$return = array();

		foreach ($files as $key => $value)
		{
			if ($value instanceof UploadedFileInterface)
			{
				$return[$key] = $value;

				continue;
			}

			// Nested files
			if (is_array($value) && isset($value['tmp_name']))
			{
				$return[$key] = static::createUploadedFile($value);

				continue;
			}

			// get next level files
			if (is_array($value))
			{
				$return[$key] = static::prepareFiles($value);

				continue;
			}

			throw new \InvalidArgumentException('Invalid value in files specification');
		}

		return $return;
	}

	/**
	 * Marshal headers from $_SERVER
	 *
	 * @param array $server
	 *
	 * @return array
	 */
	public static function prepareHeaders(array $server)
	{
		$headers = array();
		
		foreach ($server as $key => $value)
		{
			if ($value && strpos($key, 'HTTP_') === 0)
			{
				$name = strtr(substr($key, 5), '_', ' ');
				$name = strtr(ucwords(strtolower($name)), ' ', '-');
				$name = strtolower($name);

				$headers[$name] = $value;
				
				continue;
			}

			if ($value && strpos($key, 'CONTENT_') === 0)
			{
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
	 * @param array $server
	 * @param array $headers
	 *
	 * @return PsrUri
	 */
	public static function prepareUri(array $server, array $headers)
	{
		$uri = new PsrUri('');

		// URI scheme
		$scheme = 'http';
		$https  = ServerHelper::getValue($server, 'HTTPS');

		// Is https or not
		if (($https && $https !== 'off') || HeaderHelper::getValue($headers, 'x-forwarded-proto', false) === 'https')
		{
			$scheme = 'https';
		}

		// URI host
		static::getHostAndPortFromHeaders($host = '', $port = null, $server, $headers);

		// URI path
		$path = static::getRequestUri($server);
		$path = static::stripQueryString($path);

		// URI query
		$query = '';

		if (isset($server['QUERY_STRING']))
		{
			$query = ltrim($server['QUERY_STRING'], '?');
		}

		// URI fragment
		$fragment = '';

		if (strpos($path, '#') !== false)
		{
			list($path, $fragment) = explode('#', $path, 2);
		}

		return $uri->withScheme($scheme)
			->withHost($host)
			->withPort($port)
			->withPath($path)
			->withFragment($fragment)
			->withQuery($query);
	}

	/**
	 * Marshal the host and port from HTTP headers and/or the PHP environment
	 *
	 * @param \stdClass $accumulator
	 * @param array     $server
	 * @param array     $headers
	 */
	public static function getHostAndPortFromHeaders(&$host, &$port, array $server, array $headers)
	{
		if (HeaderHelper::getValue($headers, 'host', false))
		{
			static::getHostAndPortFromHeader($host, $port, HeaderHelper::getValue($headers, 'host'));

			return;
		}

		if (!isset($server['SERVER_NAME']))
		{
			return;
		}

		$host = $server['SERVER_NAME'];

		if (isset($server['SERVER_PORT']))
		{
			$port = (int) $server['SERVER_PORT'];
		}

		if (!isset($server['SERVER_ADDR']) || !preg_match('/^\[[0-9a-fA-F\:]+\]$/', $host))
		{
			return;
		}

		// Handle Ipv6
		$host = '[' . $server['SERVER_ADDR'] . ']';
		$port = $port ? : 80;

		if ($port . ']' === substr($host, strrpos($host, ':') + 1))
		{
			// The last digit of the IPv6-Address has been taken as port
			// Unset the port so the default port can be used
			$port = null;
		}
	}

	/**
	 * Detect the base URI for the request
	 *
	 * Looks at a variety of criteria in order to attempt to autodetect a base
	 * URI, including rewrite URIs, proxy URIs, etc.
	 *
	 * From ZF2's Zend\Http\PhpEnvironment\Request class
	 *
	 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
	 * @license   http://framework.zend.com/license/new-bsd New BSD License
	 *
	 * @param array $server
	 *
	 * @return string
	 */
	public static function getRequestUri(array $server)
	{
		// IIS7 with URL Rewrite: make sure we get the unencoded url
		// (double slash problem).
		$iisUrlRewritten = ServerHelper::getValue($server, 'IIS_WasUrlRewritten');
		$unencodedUrl    = ServerHelper::getValue($server, 'UNENCODED_URL', '');

		if ('1' == $iisUrlRewritten && !empty($unencodedUrl))
		{
			return $unencodedUrl;
		}

		$requestUri = ServerHelper::getValue($server, 'REQUEST_URI');

		// Check this first so IIS will catch.
		$httpXRewriteUrl = ServerHelper::getValue($server, 'HTTP_X_REWRITE_URL');

		if ($httpXRewriteUrl !== null)
		{
			$requestUri = $httpXRewriteUrl;
		}

		// Check for IIS 7.0 or later with ISAPI_Rewrite
		$httpXOriginalUrl = ServerHelper::getValue($server, 'HTTP_X_ORIGINAL_URL');

		if ($httpXOriginalUrl !== null)
		{
			$requestUri = $httpXOriginalUrl;
		}

		if ($requestUri !== null)
		{
			return preg_replace('#^[^/:]+://[^/]+#', '', $requestUri);
		}

		$origPathInfo = ServerHelper::getValue($server, 'ORIG_PATH_INFO');

		if (empty($origPathInfo))
		{
			return '/';
		}

		return $origPathInfo;
	}

	/**
	 * Strip the query string from a path
	 *
	 * @param mixed $path
	 *
	 * @return string
	 */
	public static function stripQueryString($path)
	{
		$qMark = strpos($path, '?');
			
		if ($qMark !== false)
		{
			return substr($path, 0, $qMark);
		}

		return $path;
	}

	/**
	 * Marshal the host and port from the request header
	 *
	 * @param \stdClass     $accumulator
	 * @param string|array  $host
	 *
	 * @return void
	 */
	protected static function getHostAndPortFromHeader(&$host, &$port, $headerHost)
	{
		if (is_array($headerHost))
		{
			$host = implode(', ', $headerHost);
		}

		if (preg_match('|\:(\d+)$|', $host, $matches))
		{
			$host = substr($host, 0, -1 * (strlen($matches[1]) + 1));
			$port = (int) $matches[1];
		}
	}

	/**
	 * Create and return an UploadedFile instance from a $_FILES specification.
	 *
	 * If the specification represents an array of values, this method will
	 * delegate to normalizeNestedFileSpec() and return that return value.
	 *
	 * @param array $value $_FILES struct
	 *
	 * @return UploadedFileInterface|UploadedFileInterface[]
	 */
	private static function createUploadedFile(array $value)
	{
		// Flatten file if is nested.
		if (is_array($value['tmp_name']))
		{
			return static::getFlattenFileData($value);
		}

		return new UploadedFile(
			$value['tmp_name'],
			$value['size'],
			$value['error'],
			$value['name'],
			$value['type']
		);
	}

	/**
	 * Normalize an array of file specifications.
	 *
	 * Loops through all nested files and returns a normalized array of
	 * UploadedFileInterface instances.
	 *
	 * @param array $files
	 *
	 * @return UploadedFileInterface[]
	 */
	protected static function getFlattenFileData(array $files = array())
	{
		$return = array();

		foreach (array_keys($files['tmp_name']) as $key)
		{
			$file = array(
				'tmp_name' => $files['tmp_name'][$key],
				'size'     => $files['size'][$key],
				'error'    => $files['error'][$key],
				'name'     => $files['name'][$key],
				'type'     => $files['type'][$key],
			);

			$return[$key] = self::createUploadedFile($file);
		}

		return $return;
	}

	/**
	 * Return HTTP protocol version (X.Y)
	 *
	 * @param array $server
	 *
	 * @return string
	 */
	private static function getProtocolVersion(array $server)
	{
		if (!isset($server['SERVER_PROTOCOL']))
		{
			return '1.1';
		}

		if (!preg_match('/^(HTTP\/)?(\d+(?:\.\d+)+)/', $server['SERVER_PROTOCOL'], $matches))
		{
			throw new \UnexpectedValueException(sprintf(
				'Invalid protocol version format (%s)',
				$server['SERVER_PROTOCOL']
			));
		}

		return $matches['version'];
	}
}
