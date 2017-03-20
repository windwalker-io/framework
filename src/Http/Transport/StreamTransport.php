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
use Windwalker\Http\Helper\StreamHelper;
use Windwalker\Http\Response\Response;
use Windwalker\Http\Stream\Stream;

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
	 * @param   RequestInterface  $request  The request object to store request params.
	 *
	 * @return  ResponseInterface
	 *
	 * @since   2.1
	 */
	protected function doRequest(RequestInterface $request)
	{
		// Create the stream context options array with the required method offset.
		$options = array('method' => $request->getMethod());

		// Set HTTP Version
		$options['protocol_version'] = $request->getProtocolVersion();

		// If data exists let's encode it and make sure our Content-Type header is set.
		$data = (string) $request->getBody();

		if (isset($data))
		{
			// If the data is a scalar value simply add it to the stream context options.
			if (is_scalar($data))
			{
				$options['content'] = $data;
			}
			else
			// Otherwise we need to encode the value first.
			{
				$options['content'] = http_build_query($data);
			}

			if (!$request->getHeader('Content-Type'))
			{
				$request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
			}

			// Add the relevant headers.
			$request = $request->withHeader('Content-Length', (string) strlen($options['content']));
		}

		// Speed up stream get URL
		// @see http://stackoverflow.com/questions/3629504/php-file-get-contents-very-slow-when-using-full-url
		// @see http://stackoverflow.com/questions/13679976/how-to-speed-up-file-get-contents
		$request = $request->withHeader('Connection', 'Close');

		// Build the headers string for the request.
		if ($headers = $request->getHeaders())
		{
			// Add the headers string into the stream context options array.
			$options['header'] = HeaderHelper::toHeaderLine($headers, true);
		}

		// If an explicit timeout is given user it.
		if ($this->getOption('timeout'))
		{
			$options['timeout'] = (int) $this->getOption('timeout');
		}

		// If an explicit user agent is given use it.
		if ($this->getOption('userAgent'))
		{
			$options['user_agent'] = $this->getOption('userAgent');
		}

		// Ignore HTTP errors so that we can capture them.
		$options['ignore_errors'] = 1;

		// Follow redirects.
		$options['follow_location'] = (int) $this->getOption('follow_location', 1);

		foreach ((array) $this->getOption('options') as $key => $value)
		{
			$options[$key] = $value;
		}

		// Create the stream context for the request.
		$context = stream_context_create(array('http' => $options));

		// Capture PHP errors
		$php_errormsg = '';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		$connection = @fopen($request->getRequestTarget(), Stream::MODE_READ_ONLY_FROM_BEGIN, false, $context);

		if (!$connection)
		{
			if (!$php_errormsg)
			{
				// Error but nothing from php? Create our own
				$php_errormsg = sprintf('Could not connect to resource: %s', $request->getRequestTarget());
			}

			// Restore error tracking to give control to the exception handler
			ini_set('track_errors', $track_errors);

			throw new \RuntimeException($php_errormsg);
		}

		$stream = new Stream($connection);

		if ($dest = $this->getOption('target_file'))
		{
			$content = '';
			StreamHelper::copyTo($stream, $dest);
		}
		else
		{
			$content = $stream->getContents();
		}

		$metadata = $stream->getMetadata();

		$stream->close();

		if (isset($metadata['wrapper_data']['headers']))
		{
			$headers = $metadata['wrapper_data']['headers'];
		}
		elseif (isset($metadata['wrapper_data']))
		{
			$headers = $metadata['wrapper_data'];
		}
		else
		{
			$headers = array();
		}

		return $this->getResponse($headers, $content);
	}

	/**
	 * Method to get a response object from a server response.
	 *
	 * @param   array   $headers  The response headers as an array.
	 * @param   string  $body     The response body as a string.
	 *
	 * @return  Response
	 *
	 * @since   2.1
	 * @throws  \UnexpectedValueException
	 */
	protected function getResponse(array $headers, $body)
	{
		// Create the response object.
		$return = new Response;

		// Set the body for the response.
		$return->getBody()->write($body);

		$return->getBody()->rewind();

		// Get the response code from the first offset of the response headers.
		preg_match('/[0-9]{3}/', array_shift($headers), $matches);
		$code = $matches[0];

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
			throw new \InvalidArgumentException('Target file path is emptty.');
		}

		$dest = $dest instanceof StreamInterface ? $dest : new Stream($dest, Stream::MODE_READ_WRITE_RESET);

		$this->setOption('target_file', $dest);

		$response = $this->request($request);

		$this->setOption('target_file', null);

		$dest->close();

		return $response;
	}

	/**
	 * Method to check if HTTP transport layer available for using
	 *
	 * @return  boolean  True if available else false
	 *
	 * @since   2.0
	 */
	public static function isSupported()
	{
		return function_exists('fopen') && is_callable('fopen') && ini_get('allow_url_fopen');
	}
}
