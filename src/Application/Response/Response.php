<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Application\Response;


class Response
{
	/**
	 * Property cachable.
	 *
	 * @var boolean
	 */
	protected $cachable = false;

	/**
	 * Property headers.
	 *
	 * @var  array
	 */
	protected $headers = array();

	/**
	 * Property body.
	 *
	 * @var  string
	 */
	protected $body = null;

	/**
	 * Checks the accept encoding of the browser and compresses the data before
	 * sending it to the client if possible.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function compress()
	{
		// Supported compression encodings.
		$supported = array(
			'x-gzip' => 'gz',
			'gzip' => 'gz',
			'deflate' => 'deflate'
		);

		// Get the supported encoding.
		$encodings = array_intersect($this->client->encodings, array_keys($supported));

		// If no supported encoding is detected do nothing and return.
		if (empty($encodings))
		{
			return;
		}

		// Verify that headers have not yet been sent, and that our connection is still alive.
		if ($this->checkHeadersSent() || !$this->checkConnectionAlive())
		{
			return;
		}

		// Iterate through the encodings and attempt to compress the data using any found supported encodings.
		foreach ($encodings as $encoding)
		{
			if (($supported[$encoding] == 'gz') || ($supported[$encoding] == 'deflate'))
			{
				// Verify that the server supports gzip compression before we attempt to gzip encode the data.
				// @codeCoverageIgnoreStart
				if (!extension_loaded('zlib') || ini_get('zlib.output_compression'))
				{
					continue;
				}

				// @codeCoverageIgnoreEnd

				// Attempt to gzip encode the data with an optimal level 4.
				$data = $this->getBody();
				$gzdata = gzencode($data, 4, ($supported[$encoding] == 'gz') ? FORCE_GZIP : FORCE_DEFLATE);

				// If there was a problem encoding the data just try the next encoding scheme.
				// @codeCoverageIgnoreStart
				if ($gzdata === false)
				{
					continue;
				}

				// @codeCoverageIgnoreEnd

				// Set the encoding headers.
				$this->setHeader('Content-Encoding', $encoding);
				$this->setHeader('X-Content-Encoded-By', 'Joomla');

				// Replace the output with the encoded data.
				$this->setBody($gzdata);

				// Compression complete, let's break out of the loop.
				break;
			}
		}
	}

	/**
	 * getCachable
	 *
	 * @param boolean $cachable
	 *
	 * @return  boolean
	 */
	public function isCachable($cachable = null)
	{
		if (is_bool($cachable))
		{
			$this->cachable = $cachable;

			return $cachable;
		}

		return $this->cachable;
	}

	/**
	 * Method to set a response header.  If the replace flag is set then all headers
	 * with the given name will be replaced by the new one.  The headers are stored
	 * in an internal array to be sent when the site is sent to the browser.
	 *
	 * @param   string   $name     The name of the header to set.
	 * @param   string   $value    The value of the header to set.
	 * @param   boolean  $replace  True to replace any headers with the same name.
	 *
	 * @return  Response  Instance of $this to allow chaining.
	 *
	 * @since   1.0
	 */
	public function setHeader($name, $value, $replace = false)
	{
		// Sanitize the input values.
		$name = (string) $name;
		$value = (string) $value;

		// If the replace flag is set, unset all known headers with the given name.
		if ($replace)
		{
			foreach ($this->headers as $key => $header)
			{
				if ($name == $header['name'])
				{
					unset($this->headers[$key]);
				}
			}

			// Clean up the array as unsetting nested arrays leaves some junk.
			$this->headers = array_values($this->headers);
		}

		// Add the header to the internal array.
		$this->headers[] = array('name' => $name, 'value' => $value);

		return $this;
	}

	/**
	 * Method to clear any set response headers.
	 *
	 * @return  Response  Instance of $this to allow chaining.
	 *
	 * @since   1.0
	 */
	public function clearHeaders()
	{
		$this->headers = array();

		return $this;
	}

	/**
	 * getHeaders
	 *
	 * @return  array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * setHeaders
	 *
	 * @param   array $headers
	 *
	 * @return  Response  Return self to support chaining.
	 */
	public function setHeaders($headers)
	{
		$this->headers = $headers;

		return $this;
	}

	/**
	 * Return the body content
	 *
	 * @param   boolean  $asArray  True to return the body as an array of strings.
	 *
	 * @return  mixed  The response body either as an array or concatenated string.
	 *
	 * @since   1.0
	 */
	public function getBody($asArray = false)
	{
		return $asArray ? $this->body : implode((array) $this->body);
	}

	/**
	 * Set body content.  If body content already defined, this will replace it.
	 *
	 * @param   string  $content  The content to set as the response body.
	 *
	 * @return  Response  Instance of $this to allow chaining.
	 *
	 * @since   1.0
	 */
	public function setBody($content)
	{
		$this->response->body = array((string) $content);

		return $this;
	}


	/**
	 * Prepend content to the body content
	 *
	 * @param   string  $content  The content to prepend to the response body.
	 *
	 * @return  Response  Instance of $this to allow chaining.
	 *
	 * @since   1.0
	 */
	public function prependBody($content)
	{
		array_unshift($this->body, (string) $content);

		return $this;
	}

	/**
	 * Append content to the body content
	 *
	 * @param   string  $content  The content to append to the response body.
	 *
	 * @return  Response  Instance of $this to allow chaining.
	 *
	 * @since   1.0
	 */
	public function appendBody($content)
	{
		array_push($this->body, (string) $content);

		return $this;
	}

	/**
	 * Method to send a header to the client.  We are wrapping this to isolate the header() function
	 * from our code base for testing reasons.
	 *
	 * @param   string   $string   The header string.
	 * @param   boolean  $replace  The optional replace parameter indicates whether the header should
	 *                             replace a previous similar header, or add a second header of the same type.
	 * @param   integer  $code     Forces the HTTP response code to the specified value. Note that
	 *                             this parameter only has an effect if the string is not empty.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @see     header()
	 * @since   1.0
	 */
	protected function header($string, $replace = true, $code = null)
	{
		header($string, $replace, $code);
	}
}
 