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

/**
 * The AbstractTransport class.
 * 
 * @since  2.1
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
		$this->setOptions($options);
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
			->withPath('')
			->withQuery('')
			->withFragment('');

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
	 * Get option value.
	 *
	 * @param   string  $name     Option name.
	 * @param   mixed   $default  The default value if not exists.
	 *
	 * @return  mixed  The found value or default value.
	 */
	public function getOption($name, $default = null)
	{
		if (!isset($this->options[$name]))
		{
			return $default;
		}

		return $this->options[$name];
	}

	/**
	 * Set option value.
	 *
	 * @param   string  $name   Option name.
	 * @param   mixed   $value  The value you want to set in.
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setOption($name, $value)
	{
		$this->options[$name] = $value;

		return $this;
	}

	/**
	 * Method to get property Options
	 *
	 * @return  array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Method to set property options
	 *
	 * @param   array $options
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setOptions($options)
	{
		if ($options instanceof \Traversable)
		{
			$options = iterator_to_array($options);
		}

		if (is_object($options))
		{
			$options = get_object_vars($options);
		}

		$this->options = (array) $options;

		return $this;
	}
}
