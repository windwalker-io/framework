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
	 * @since   2.1
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
	 * @param   RequestInterface $request The request object to send.
	 *
	 * @return  ResponseInterface
	 *
	 * @since   2.1
	 */
	public function request(RequestInterface $request)
	{
		$uri = $request->getUri()
			->withPath(null)
			->withQuery(null)
			->withFragment(null);

		$uri = $uri . $request->getRequestTarget();

		$request = $request->withRequestTarget($uri);

		return $this->doRequest($request);
	}

	/**
	 * Send a request to the server and return a Response object with the response.
	 *
	 * @param   RequestInterface  $request  The request object to store request params.
	 *
	 * @return  ResponseInterface
	 *
	 * @since   2.1
	 */
	abstract protected function doRequest(RequestInterface $request);

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
