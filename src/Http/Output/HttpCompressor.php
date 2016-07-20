<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Output;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Windwalker\Http\Stream\Stream;

/**
 * The HttpCompressor class to support gzip encoding.
 *
 * @since  3.0
 */
class HttpCompressor
{
	const ENCODING_GZIP    = 'gz';
	const ENCODING_XGZIP   = 'gz';
	const ENCODING_DEFLATE = 'deflate';

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
	 * Property encodedBy.
	 *
	 * @var  string
	 */
	protected $encodedBy = 'Windwalker';

	/**
	 * Compressor constructor.
	 *
	 * @param  string  $acceptEncoding  The Accept-Encoding value, most is "gzip, deflate".
	 *                                  Keep null to get it from globals.
	 */
	public function __construct($acceptEncoding = null)
	{
		$this->acceptEncoding = $acceptEncoding ? : $this->getAcceptEncoding();
	}

	/**
	 * Method to check zlib supported.
	 *
	 * @return  boolean
	 */
	public static function isSupported()
	{
		return extension_loaded('zlib') || ini_get('zlib.output_compression');
	}

	/**
	 * Method to parse Accept-Encoding to an array thar we can use it when compressing data.
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
	 * @param   ResponseInterface  $response  The Response object contains the data we want to encode.
	 *
	 * @return  ResponseInterface  Return Response object.
	 *
	 * @throws  CompressException
	 *
	 * @since   3.0
	 */
	public function compress(ResponseInterface $response)
	{
		$this->parseEncodings();

		// Supported compression encodings.
		$supported = array(
			'x-gzip'  => FORCE_GZIP,
			'gzip'    => FORCE_GZIP,
			'deflate' => FORCE_DEFLATE
		);

		// Get the supported encoding.
		$encodings = array_intersect($this->encodings, array_keys($supported));

		// If no supported encoding is detected do nothing and return.
		if (empty($encodings))
		{
			return $response;
		}

		// Iterate through the encodings and attempt to compress the data using any found supported encodings.
		foreach ($encodings as $encoding)
		{
			// Attempt to gzip encode the data with an optimal level 4.
			$data = $response->getBody();
			$gzdata = $this->encode($data, $supported[$encoding]);

			// If there was a problem encoding the data just try the next encoding scheme.
			if ($gzdata === false)
			{
				continue;
			}

			// Set the encoding headers.
			/** @var ResponseInterface|MessageInterface $response */
			$response = $response->withHeader('Content-Encoding', $encoding);
			$response = $response->withHeader('X-Content-Encoded-By', $this->getEncodedBy());

			// Replace the output with the encoded data.
			$response = $response->withBody(new Stream('php://memory', Stream::MODE_READ_WRITE_FROM_BEGIN));
			$response->getBody()->write($gzdata);

			// Compression complete, let's break out of the loop.
			break;
		}

		return $response;
	}

	/**
	 * Compress raw data.
	 *
	 * @param string  $data      The data to encode.
	 * @param int     $encoding  The encoding mode. Can be FORCE_GZIP (the default) or FORCE_DEFLATE.
	 * @param int     $level     The level of compression. Can be given as 0 for no compression up to 9
	 *                           for maximum compression. If not given, the default compression level will
	 *                           be the default compression level of the zlib library.
	 *
	 * @return  string
	 * 
	 * @throws  CompressException
	 */
	public function encode($data, $encoding = FORCE_GZIP, $level = 4)
	{
		// Verify that the server supports gzip compression before we attempt to gzip encode the data.
		if (!static::isSupported())
		{
			throw new CompressException(
				'Your system does not support HTTP compression, please check zlib has benn enabled' .
				' or zlib.output_compression in php.ini has set to "On".'
			);
		}

		// Verify that headers have not yet been sent, and that our connection is still alive.
		if ($this->checkHeadersSent())
		{
			throw new CompressException('Header has been sent or connection is not alive, compression can not work.');
		}

		if (!$this->checkConnectionAlive())
		{
			throw new CompressException('Connection is not alive, compression can not work.');
		}

		return gzencode($data, $level, $encoding);
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
			$this->acceptEncoding = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';
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

	/**
	 * Method to get property EncodedBy
	 *
	 * @return  string
	 */
	public function getEncodedBy()
	{
		return $this->encodedBy;
	}

	/**
	 * Method to set property encodedBy
	 *
	 * @param   string  $encodedBy
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setEncodedBy($encodedBy)
	{
		$this->encodedBy = (string) $encodedBy;

		return $this;
	}
}
