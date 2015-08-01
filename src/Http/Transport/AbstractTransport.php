<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Http\Transport;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * The AbstractTransport class.
 * 
 * @since  {DEPLOY_VERSION}
 */
abstract class AbstractTransport implements TransportInterface
{
	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = array();

	/**
	 * Constructor.
	 *
	 * @param   array|\ArrayAccess $options Client options object.
	 *
	 * @since   1.0
	 */
	public function __construct($options = array())
	{
		if (!static::isSupported())
		{
			throw new \RangeException(__CLASS__ . ' not support.');
		}

		if (!is_array($options) && !($options instanceof \ArrayAccess))
		{
			throw new \InvalidArgumentException(
				'The options param must be an array or implement the ArrayAccess interface.'
			);
		}

		$this->options = $options;
	}

	/**
	 * Send a request to the server and return a Response object with the response.
	 *
	 * @param  RequestInterface $request The request object to send.
	 *
	 * @return ResponseInterface
	 * @since   1.0
	 */
	public function request(RequestInterface $request)
	{
		$method = $request->getMethod();

		$uri = $request->getUri()
			->withPath(null)
			->withQuery(null)
			->withFragment(null);

		$uri = $uri . $request->getRequestTarget();

		$data = $request->getBody()->getContents();

		return $this->doRequest($method, $uri, json_decode($data));
	}

	/**
	 * Send a request to the server and return a Response object with the response.
	 *
	 * @param   string        $method     The HTTP method for sending the request.
	 * @param   string        $uri        The URI to the resource to request.
	 * @param   mixed         $data       Either an associative array or a string to be sent with the request.
	 * @param   array         $headers    An array of request headers to send with the request.
	 * @param   integer       $timeout    Read timeout in seconds.
	 * @param   string        $userAgent  The optional user agent string to send with the request.
	 *
	 * @return  ResponseInterface
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	abstract protected function doRequest($method, $uri, $data = null, array $headers = null, $timeout = null, $userAgent = null);

	/**
	 * getOption
	 *
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return  mixed
	 */
	public function getOption($name, $default = null)
	{
		if (!isset($this->options[$name]))
		{
			return $default;
		}

		return $this->options[$name];
	}
}
