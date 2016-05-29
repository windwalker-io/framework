<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Output;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Windwalker\Http\WebServer;

/**
 * The Compressor class.
 *
 * @since  {DEPLOY_VERSION}
 */
class Compressor
{
	/**
	 * Property request.
	 *
	 * @var  WebServer
	 */
	protected $server;

	/**
	 * Property acceptEncoding.
	 *
	 * @var  string
	 */
	protected $acceptEncoding;

	/**
	 * Property encoding.
	 *
	 * @var  array
	 */
	protected $encodings;

	/**
	 * Compressor constructor.
	 *
	 * @param WebServer $server
	 * @param string    $acceptEncoding
	 */
	public function __construct(WebServer $server, $acceptEncoding = null)
	{
		$this->server         = $server;
		$this->acceptEncoding = $acceptEncoding ? : $this->getAcceptEncoding();
	}

	/**
	 * parseEncoding
	 *
	 * @return  void
	 */
	protected function parseEncodings()
	{
		$this->encodings = array_map('trim', (array) explode(',', $this->getAcceptEncoding()));
	}

	/**
	 * Checks the accept encoding of the browser and compresses the data before
	 * sending it to the client if possible.
	 *
	 * @param ResponseInterface $response
	 *
	 * @return static Return self to support chaining.
	 *
	 * @since    3.0
	 */
	public function compress(ResponseInterface $response)
	{
		$this->parseEncodings();

		// Supported compression encodings.
		$supported = array(
			'x-gzip'  => 'gz',
			'gzip'    => 'gz',
			'deflate' => 'deflate'
		);

		// Get the supported encoding.
		$encodings = array_intersect($this->encodings, array_keys($supported));

		// If no supported encoding is detected do nothing and return.
		if (empty($encodings))
		{
			return $response;
		}

		// Verify that headers have not yet been sent, and that our connection is still alive.
		if ($this->checkHeadersSent() || !$this->checkConnectionAlive())
		{
			return $response;
		}

		// Iterate through the encodings and attempt to compress the data using any found supported encodings.
		foreach ($encodings as $encoding)
		{
			if (($supported[$encoding] == 'gz') || ($supported[$encoding] == 'deflate'))
			{
				// Verify that the server supports gzip compression before we attempt to gzip encode the data.
				if (!extension_loaded('zlib') || ini_get('zlib.output_compression'))
				{
					continue;
				}

				// Attempt to gzip encode the data with an optimal level 4.
				$data = $response->getBody();
				$gzdata = gzencode($data, 4, ($supported[$encoding] == 'gz') ? FORCE_GZIP : FORCE_DEFLATE);

				// If there was a problem encoding the data just try the next encoding scheme.
				if ($gzdata === false)
				{
					continue;
				}

				// Set the encoding headers.
				/** @var ResponseInterface|MessageInterface $response */
				$response = $response->withHeader('content-encoding', $encoding);
				$response = $response->withHeader('x-content-encoded-by', 'Windwalker');

				// Replace the output with the encoded data.
				$body = $response->getBody();
				$body->rewind();
				$body->write($gzdata);

				// Compression complete, let's break out of the loop.
				break;
			}
		}

		return $response;
	}

	/**
	 * Method to get property Request
	 *
	 * @return  WebServer
	 */
	public function getServer()
	{
		return $this->server;
	}

	/**
	 * Method to set property request
	 *
	 * @param   WebServer $server
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setServer(WebServer $server)
	{
		$this->server = $server;

		return $this;
	}

	/**
	 * Method to get property AcceptEncoding
	 *
	 * @return  string
	 */
	public function getAcceptEncoding()
	{
		if ($this->acceptEncoding === null)
		{
			$server = $this->getServer()->getRequest()->getServerParams();

			$this->acceptEncoding = isset($server['HTTP_ACCEPT_ENCODING']) ? $server['HTTP_ACCEPT_ENCODING'] : $server['HTTP_ACCEPT_ENCODING'];
		}

		return $this->acceptEncoding;
	}

	/**
	 * Method to set property acceptEncoding
	 *
	 * @param   string $acceptEncoding
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setAcceptEncoding($acceptEncoding)
	{
		$this->acceptEncoding = $acceptEncoding;

		return $this;
	}

	/**
	 * Method to check to see if headers have already been sent.
	 * We wrap headers_sent() function with this method for testing reason.
	 *
	 * @return  boolean  True if the headers have already been sent.
	 *
	 * @see     headers_sent()
	 * @since   2.0
	 */
	public function checkHeadersSent()
	{
		return headers_sent();
	}

	/**
	 * Method to check the current client connection status to ensure that it is alive.
	 * We wrap connection_status() function with this method for testing reason.
	 *
	 * @return  boolean  True if the connection is valid and normal.
	 *
	 * @see     connection_status()
	 * @since   2.0
	 */
	public function checkConnectionAlive()
	{
		return (connection_status() === CONNECTION_NORMAL);
	}
}
