<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Http\Transport;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Helper\HeaderHelper;
use Windwalker\Http\Response\Response;

/**
 * The CurlTransport class.
 * 
 * @since  2.1
 */
class CurlTransport extends AbstractTransport
{
	/**
	 * Send a request to the server and return a Response object with the response.
	 *
	 * @param   RequestInterface  $request  The request object to store request params.
	 *
	 * @return  ResponseInterface
	 *
	 * @since    2.1
	 */
	protected function doRequest(RequestInterface $request)
	{
		// Setup the cURL handle.
		$ch = curl_init();

		// Set the request method.
		$options[CURLOPT_CUSTOMREQUEST] = $request->getMethod();

		// Don't wait for body when $method is HEAD
		$options[CURLOPT_NOBODY] = ($request->getMethod() === 'HEAD');

		// Initialize the certificate store
		$options[CURLOPT_CAINFO] = $this->getOption('certpath',  __DIR__ . '/cacert.pem');

		// Set HTTP Version
		switch ($request->getProtocolVersion())
		{
			case '1.0':
				$options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_0;
				break;

			case '1.1':
				$options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
				break;

			case '2':
				if (defined('CURL_HTTP_VERSION_2_0'))
				{
					$options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_2_0;
				}
		}

		// If data exists let's encode it and make sure our Content-type header is set.
		$data = (string) $request->getBody();

		if (isset($data))
		{
			// If the data is a scalar value simply add it to the cURL post fields.
			if (is_scalar($data) || strpos($request->getHeaderLine('Content-Type'), 'multipart/form-data') === 0)
			{
				$options[CURLOPT_POSTFIELDS] = $data;
			}
			else
			// Otherwise we need to encode the value first.
			{
				$options[CURLOPT_POSTFIELDS] = http_build_query($data);
			}

			if (!$request->getHeaderLine('Content-Type'))
			{
				$request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
			}

			// Add the relevant headers.
			if (is_scalar($options[CURLOPT_POSTFIELDS]))
			{
				$request = $request->withHeader('Content-Length', (string) strlen($options[CURLOPT_POSTFIELDS]));
			}
		}

		// Build the headers string for the request.
		if ($headers = $request->getHeaders())
		{
			// Add the headers string into the stream context options array.
			$options[CURLOPT_HTTPHEADER] = HeaderHelper::toHeaderLine($headers);
		}

		// If an explicit timeout is given user it.
		if ($timeout = $this->getOption('timeout'))
		{
			$options[CURLOPT_TIMEOUT] = (int) $timeout;
			$options[CURLOPT_CONNECTTIMEOUT] = (int) $timeout;
		}

		// If an explicit user agent is given use it.
		if ($userAgent = $this->getOption('userAgent'))
		{
			$options[CURLOPT_USERAGENT] = $userAgent;
		}

		// Set the request URL.
		$options[CURLOPT_URL] = (string) $request->getRequestTarget();

		// We want our headers. :-)
		$options[CURLOPT_HEADER] = true;

		// Return it... echoing it would be tacky.
		$options[CURLOPT_RETURNTRANSFER] = true;

		// Override the Expect header to prevent cURL from confusing itself in its own stupidity.
		// Link: http://the-stickman.com/web-development/php-and-curl-disabling-100-continue-header/
		$options[CURLOPT_HTTPHEADER][] = 'Expect:';

		/*
		 * Follow redirects if server config allows
		 * @deprecated  safe_mode is removed in PHP 5.4, check will be dropped when PHP 5.3 support is dropped
		 */
		if (!ini_get('safe_mode') && !ini_get('open_basedir'))
		{
			$options[CURLOPT_FOLLOWLOCATION] = (bool) isset($this->options['follow_location']) ? $this->options['follow_location'] : true;
		}

		// Set any custom transport options
		if ($this->getOption('options'))
		{
			foreach ((array) $this->getOption('options') as $key => $value)
			{
				$options[$key] = $value;
			}
		}

		// Set the cURL options.
		curl_setopt_array($ch, $options);

		// Execute the request and close the connection.
		$content = curl_exec($ch);

		if (!$this->getOption('allow_empty_result', false) && !trim($content))
		{
			$message = curl_error($ch);

			// Error but nothing from cURL? Create our own
			$message = $message ? : 'No HTTP response received';

			throw new \RuntimeException($message);
		}

		// Get the request information.
		$info = curl_getinfo($ch);

		// Close the connection.
		curl_close($ch);

		return $this->getResponse($content, $info);
	}

	/**
	 * Method to get a response object from a server response.
	 *
	 * @param   string  $content  The complete server response, including headers
	 *                            as a string if the response has no errors.
	 * @param   array   $info     The cURL request information.
	 *
	 * @return  Response
	 *
	 * @since   2.0
	 * @throws  \UnexpectedValueException
	 */
	protected function getResponse($content, $info)
	{
		// Create the response object.
		$return = new Response;

		// Get the number of redirects that occurred.
		$redirects = isset($info['redirect_count']) ? $info['redirect_count'] : 0;

		/*
		 * Split the response into headers and body. If cURL encountered redirects, the headers for the redirected requests will
		 * also be included. So we split the response into header + body + the number of redirects and only use the last two
		 * sections which should be the last set of headers and the actual body.
		 */
		$response = explode("\r\n\r\n", $content, 2 + $redirects);

		// Set the body for the response.
		$return->getBody()->write(array_pop($response));

		$return->getBody()->rewind();

		// Get the last set of response headers as an array.
		$headers = explode("\r\n", array_pop($response));

		// Get the response code from the first offset of the response headers.
		preg_match('/[0-9]{3}/', array_shift($headers), $matches);

		$code = count($matches) ? $matches[0] : null;

		if (is_numeric($code))
		{
			$return = $return->withStatus($code);
		}

		// No valid response code was detected.
		else
		{
			throw new \UnexpectedValueException('No HTTP response code found.');
		}

		// Add the response headers to the response object.
		foreach ($headers as $header)
		{
			$pos = strpos($header, ':');

			$return = $return->withHeader(trim(substr($header, 0, $pos)), trim(substr($header, ($pos + 1))));
		}

		return $return;
	}

	/**
	 * Use stream to download file.
	 *
	 * @param   RequestInterface       $request The request object to store request params.
	 * @param   string|StreamInterface $dest    The dest path to store file.
	 *
	 * @return  ResponseInterface
	 * @since   2.1
	 */
	public function download(RequestInterface $request, $dest)
	{
		if (!$dest)
		{
			throw new \InvalidArgumentException('Target file path is empty.');
		}

		$response = $this->request($request);

		file_put_contents($dest, $response->getBody()->__toString());

		return $response;
	}

	/**
	 * Method to check if HTTP transport layer available for using
	 *
	 * @return  boolean  True if available else false
	 *
	 * @since   2.1
	 */
	public static function isSupported()
	{
		return function_exists('curl_init') && is_callable('curl_init');
	}
}
