<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Http;

if (!interface_exists('Http\Client\HttpClient'))
{
	include_once __DIR__ . '/HttpPlugClientInterface.php';
}

use Http\Client\HttpClient as HttpPlugClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Windwalker\Http\Request\Request;
use Windwalker\Http\Transport\CurlTransport;
use Windwalker\Http\Transport\TransportInterface;
use Windwalker\Uri\PsrUri;
use Windwalker\Uri\Uri;
use Windwalker\Uri\UriHelper;

/**
 * The HttpClient class.
 * 
 * @since  2.1
 */
class HttpClient implements HttpClientInterface, HttpPlugClientInterface
{
	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = array();

	/**
	 * Property transport.
	 *
	 * @var  TransportInterface
	 */
	protected $transport;

	/**
	 * Class init.
	 *
	 * @param  array               $options    The options of this client object.
	 * @param  TransportInterface  $transport  The Transport handler, default is CurlTransport.
	 */
	public function __construct($options = array(), TransportInterface $transport = null)
	{
		$this->options   = (array) $options;
		$this->transport = $transport ? : new CurlTransport;
	}

	/**
	 * Request a remote server.
	 *
	 * This method will build a Request object and use send() method to send request.
	 *
	 * @param string        $method  The method type.
	 * @param string|object $url     The URL to request, may be string or Uri object.
	 * @param mixed         $data    The request body data, can be an array of POST data.
	 * @param array         $headers The headers array.
	 *
	 * @return  ResponseInterface
	 */
	public function request($method, $url, $data = null, $headers = array())
	{
		$request = $this->prepareRequest(new Request, $method, $url, $data, $headers);

		return $this->send($request);
	}

	/**
	 * Download file to target path.
	 *
	 * @param string|object $url     The URL to request, may be string or Uri object.
	 * @param string|       $dest    The dest file path can be a StreamInterface.
	 * @param mixed         $data    The request body data, can be an array of POST data.
	 * @param array         $headers The headers array.
	 *
	 * @return  ResponseInterface
	 */
	public function download($url, $dest, $data = null, $headers = array())
	{
		$request = $this->prepareRequest(new Request, 'GET', $url, $data, $headers);

		$transport = $this->getTransport();

		if (!$transport::isSupported())
		{
			throw new \RangeException(get_class($transport) . ' driver not supported.');
		}

		return $transport->download($request, $dest);
	}

	/**
	 * Send a request to remote.
	 *
	 * @param   RequestInterface $request The Psr Request object.
	 *
	 * @return  ResponseInterface
	 */
	public function send(RequestInterface $request)
	{
		$transport = $this->getTransport();

		if (!$transport::isSupported())
		{
			throw new \RangeException(get_class($transport) . ' driver not supported.');
		}

		return $transport->request($request);
	}

	/**
	 * Method to send the OPTIONS command to the server.
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  ResponseInterface
	 *
	 * @since   2.1
	 */
	public function options($url, $headers = array())
	{
		return $this->request('OPTIONS', $url, null, $headers);
	}

	/**
	 * Method to send the HEAD command to the server.
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  ResponseInterface
	 *
	 * @since   2.1
	 */
	public function head($url, $headers = array())
	{
		return $this->request('HEAD', $url, null, $headers);
	}

	/**
	 * Method to send the GET command to the server.
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   mixed    $data     Either an associative array or a string to be sent with the request.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  ResponseInterface
	 *
	 * @since   2.1
	 */
	public function get($url, $data = null, $headers = array())
	{
		return $this->request('GET', $url, $data, $headers);
	}

	/**
	 * Method to send the POST command to the server.
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   mixed    $data     Either an associative array or a string to be sent with the request.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request
	 *
	 * @return  ResponseInterface
	 *
	 * @since   2.1
	 */
	public function post($url, $data, $headers = array())
	{
		return $this->request('POST', $url, $data, $headers);
	}

	/**
	 * Method to send the PUT command to the server.
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   mixed    $data     Either an associative array or a string to be sent with the request.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  ResponseInterface
	 *
	 * @since   2.1
	 */
	public function put($url, $data, $headers = array())
	{
		return $this->request('PUT', $url, $data, $headers);
	}

	/**
	 * Method to send the DELETE command to the server.
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   mixed    $data     Either an associative array or a string to be sent with the request.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  ResponseInterface
	 *
	 * @since   2.1
	 */
	public function delete($url, $data = null, $headers = array())
	{
		return $this->request('DELETE', $url, $data, $headers);
	}

	/**
	 * Method to send the TRACE command to the server.
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  ResponseInterface
	 *
	 * @since   2.1
	 */
	public function trace($url, $headers = array())
	{
		return $this->request('TRACE', $url, null, $headers);
	}

	/**
	 * Method to send the PATCH command to the server.
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   mixed    $data     Either an associative array or a string to be sent with the request.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  ResponseInterface
	 *
	 * @since   2.1
	 */
	public function patch($url, $data, $headers = array())
	{
		return $this->request('PATCH', $url, $data, $headers);
	}

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

	/**
	 * Method to get property Transport
	 *
	 * @return  TransportInterface
	 */
	public function getTransport()
	{
		return $this->transport;
	}

	/**
	 * Method to set property transport
	 *
	 * @param   TransportInterface $transport
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setTransport(TransportInterface $transport)
	{
		$this->transport = $transport;

		return $this;
	}

	/**
	 * Prepare Request object to send request.
	 *
	 * @param   RequestInterface  $request  The Psr Request object.
	 * @param   string            $method   The method type.
	 * @param   string|object     $url      The URL to request, may be string or Uri object.
	 * @param   mixed             $data     The request body data, can be an array of POST data.
	 * @param   array             $headers  The headers array.
	 *
	 * @return  RequestInterface
	 */
	protected function prepareRequest(RequestInterface $request, $method, $url, $data, $headers)
	{
		$url = (string) $url;

		// If is GET, we merge data into URL.
		if (strtoupper($method) == 'GET' && is_array($data))
		{
			$url = new Uri($url);

			foreach ($data as $k => $v)
			{
				$url->setVar($k, $v);
			}

			$url = (string) $url;
			$data = null;
		}

		// If not GET, convert data to query string.
		if (is_array($data))
		{
			$data = UriHelper::buildQuery($data);
		}

		/** @var RequestInterface $request */
		$request->getBody()->write((string) $data);

		$request = $request->withRequestTarget((string) new PsrUri($url))
			->withMethod($method);

		// Set global headers
		foreach ((array) $this->getOption('headers') as $key => $value)
		{
			$request = $request->withHeader($key, $value);
		}

		// Override with this method
		foreach ($headers as $key => $value)
		{
			$request = $request->withHeader($key, $value);
		}

		return $request;
	}

	/**
	 * Sends a PSR-7 request.
	 *
	 * @param RequestInterface $request
	 *
	 * @return ResponseInterface
	 *
	 * @throws \Http\Client\Exception If an error happens during processing the request.
	 * @throws \Exception             If processing the request is impossible (eg. bad configuration).
	 */
	public function sendRequest(RequestInterface $request)
	{
		return $this->send($request);
	}
}
