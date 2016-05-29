<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Output;

use Psr\Http\Message\ResponseInterface;

/**
 * The StreamOutput class.
 *
 * @since  {DEPLOY_VERSION}
 */
class StreamOutput extends Output
{
	/**
	 * Property maxBufferLength.
	 *
	 * @var  integer
	 */
	protected $maxBufferLength = 8192;

	/**
	 * Delay every loop for microseconds.
	 *
	 * @var  integer
	 */
	protected $delay = null;

	/**
	 * respond
	 *
	 * @param ResponseInterface $response
	 * @param bool              $returnBody
	 *
	 * @return  void
	 */
	public function respond(ResponseInterface $response, $returnBody = false)
	{
		if ($this->checkHeaderSent())
		{
			throw new \RuntimeException('Headers has already sent, unable to respond data.');
		}

		$response = $this->prepareContentLength($response);

		parent::respond($response, false);
	}

	/**
	 * sendBody
	 *
	 * @param ResponseInterface $response
	 *
	 * @return  void
	 */
	public function sendBody(ResponseInterface $response)
	{
		$range = $this->getContentRange($response->getHeaderLine('content-range'));

		$maxBufferLength = $this->getMaxBufferLength() ? : 8192;

		if ($range === false)
		{
			$body = $response->getBody();
			$body->rewind();

			while (!$body->eof())
			{
				echo $body->read($maxBufferLength);

				$this->delay();
			}

			return;
		}

		list($unit, $first, $last, $lenght) = $range;

		++$last;

		$body = $response->getBody();
		$body->seek($first);
		$position = $first;

		while (!$body->eof() && $position < $last)
		{
			// The latest part
			if (($position + $maxBufferLength) > $last)
			{
				echo $body->read($last - $position);

				$this->delay();

				break;
			}

			echo $body->read($maxBufferLength);

			$position = $body->tell();

			$this->delay();
		}
	}

	/**
	 * prepareContentLength
	 *
	 * @param ResponseInterface $response
	 *
	 * @return  ResponseInterface|static
	 */
	protected function prepareContentLength(ResponseInterface $response)
	{
		if (!$response->hasHeader('content-length'))
		{
			if ($response->getBody()->getSize() !== null)
			{
				return $response->withHeader('content-length', (string) $response->getBody()->getSize());
			}
		}

		return $response;
	}

	/**
	 * Parse content-range header to an array.
	 *
	 * @see  http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.16
	 *
	 * @param   string $header
	 *
	 * @return  false|array  An array with [unit, first, last, length] elements;
	 */
	protected function getContentRange($header)
	{
		if (preg_match('/(?P<unit>[\w]+)\s+(?P<first>\d+)-(?P<last>\d+)\/(?P<length>\d+|\*)/', $header, $matches))
		{
			$matches['first']  = (int) $matches['first'];
			$matches['last']   = (int) $matches['last'];

			if (is_numeric($matches['length']))
			{
				$matches['length'] = (int) $matches['length'];
			}

			return $matches;
		}

		return false;
	}

	/**
	 * Check header sent or not. The method is for for test use.
	 *
	 * @return  boolean
	 *
	 * @see  headers_sent
	 */
	public function checkHeaderSent()
	{
		return headers_sent();
	}

	/**
	 * Method to get property MaxBufferLength
	 *
	 * @return  int
	 */
	public function getMaxBufferLength()
	{
		return $this->maxBufferLength;
	}

	/**
	 * Method to set property maxBufferLength
	 *
	 * @param   int $maxBufferLength
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setMaxBufferLength($maxBufferLength)
	{
		$this->maxBufferLength = $maxBufferLength;

		return $this;
	}

	/**
	 * Method to get property Delay
	 *
	 * @return  int
	 */
	public function getDelay()
	{
		return $this->delay;
	}

	/**
	 * Method to set property delay
	 *
	 * @param   int $delay
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDelay($delay)
	{
		$this->delay = $delay;

		return $this;
	}

	/**
	 * delay
	 *
	 * @return  void
	 */
	protected function delay()
	{
		if ($this->delay === null)
		{
			return;
		}

		usleep($this->delay);
	}
}
