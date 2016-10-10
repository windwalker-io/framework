<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Http\Output;

use Psr\Http\Message\ResponseInterface;
use Windwalker\Http\Helper\HeaderHelper;

/**
 * Standard output object for PHP SAPI.
 *
 * @since  3.0
 */
class Output implements OutputInterface
{
	/**
	 * Property headerSent.
	 *
	 * @var  string
	 */
	public $headerSent = 'headers_sent';

	/**
	 * Method to send the application response to the client.  All headers will be sent prior to the main
	 * application output data.
	 *
	 * @param   ResponseInterface  $response    Respond body output.
	 * @param   boolean            $returnBody  Return body as string.
	 *
	 * @return  ResponseInterface
	 *
	 * @since   3.0
	 */
	public function respond(ResponseInterface $response, $returnBody = false)
	{
		if (!$this->headersSent())
		{
			$this->sendStatusLine($response);
			$this->sendHeaders($response);
		}

		if ($returnBody)
		{
			return $response;
		}

		$this->sendBody($response);

		return null;
	}

	/**
	 * Method to send the application response to the client.  All headers will be sent prior to the main
	 * application output data.
	 *
	 * @param ResponseInterface $response Emmit string to respond.
	 *
	 * @return string
	 */
	public function sendBody(ResponseInterface $response)
	{
		echo $response->getBody();
	}

	/**
	 * Method to send a header to the client.  We wrap header() function with this method for testing reason.
	 *
	 * @param   string   $string   The header string.
	 * @param   boolean  $replace  The optional replace parameter indicates whether the header should
	 *                             replace a previous similar header, or add a second header of the same type.
	 * @param   integer  $code     Forces the HTTP response code to the specified value. Note that
	 *                             this parameter only has an effect if the string is not empty.
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @see     header()
	 */
	public function header($string, $replace = true, $code = null)
	{
		header($string, $replace, $code);

		return $this;
	}

	/**
	 * Send all response headers.
	 *
	 * @param   ResponseInterface  $response  Response object to contain headers.
	 *
	 * @return  Output  Instance of $this to allow chaining.
	 */
	public function sendHeaders(ResponseInterface $response)
	{
		foreach ($response->getHeaders() as $header => $values)
		{
			$first  = true;
			$header = HeaderHelper::normalizeHeaderName($header);

			foreach ($values as $value)
			{
				$this->header(sprintf('%s: %s', $header, $value), $first);

				$first = false;
			}
		}

		return $this;
	}

	/**
	 * Send HTTP status line.
	 *
	 * @param   ResponseInterface  $response  Response object to contain status code and protocol version.
	 *
	 * @return  void
	 */
	public function sendStatusLine(ResponseInterface $response)
	{
		$reasonPhrase = $response->getReasonPhrase();

		$reasonPhrase = ($reasonPhrase ? ' ' . $reasonPhrase : '');

		$this->header(sprintf('HTTP/%s %d%s', $response->getProtocolVersion(), $response->getStatusCode(), $reasonPhrase));
	}

	/**
	 * checkHeaderSent
	 *
	 * @return bool
	 */
	public function headersSent()
	{
		return call_user_func($this->headerSent);
	}
}
