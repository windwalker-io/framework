<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Windwalker\Http\Output\Output;
use Windwalker\Http\Output\OutputInterface;
use Windwalker\Http\Request\ServerRequestFactory;
use Windwalker\Http\Response\Response;

/**
 * The Server class.
 *
 * @since  {DEPLOY_VERSION}
 */
class Server
{
	/**
	 * Property handler.
	 *
	 * @var  callable
	 */
	protected $handler;

	/**
	 * Property request.
	 *
	 * @var  ServerRequestInterface
	 */
	protected $request;

	/**
	 * Property response.
	 *
	 * @var  ResponseInterface
	 */
	protected $response;

	/**
	 * Property emitter.
	 *
	 * @var  OutputInterface
	 */
	protected $output;

	/**
	 * Create a Server instance
	 *
	 * Creates a server instance from the callback and the following
	 * PHP environmental values:
	 *
	 * - server; typically this will be the $_SERVER superglobal
	 * - query; typically this will be the $_GET superglobal
	 * - body; typically this will be the $_POST superglobal
	 * - cookies; typically this will be the $_COOKIE superglobal
	 * - files; typically this will be the $_FILES superglobal
	 *
	 * @param callable $callback
	 * @param array $server
	 * @param array $query
	 * @param array $body
	 * @param array $cookies
	 * @param array $files
	 * @return static
	 */
	public static function createFromGlobals(callable $callback, array $server, array $query, array $body, array $cookies, array $files)
	{
		$request  = ServerRequestFactory::create($server, $query, $body, $cookies, $files);

		return new static($callback, $request);
	}

	/**
	 * Create a Server instance from an existing request object
	 *
	 * Provided a callback, an existing request object, and optionally an
	 * existing response object, create and return the Server instance.
	 *
	 * If no Response object is provided, one will be created.
	 *
	 * @param callable          $handler
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 * @param OutputInterface   $output
	 */
	public function __construct(callable $handler, RequestInterface $request, ResponseInterface $response = null, OutputInterface $output = null)
	{
		$this->handler  = $handler;
		$this->request  = $request;
		$this->response = $response ? : new Response;
		$this->output   = $output ? : $this->getOutput();
	}

	/**
	 * listen
	 *
	 * @param callable $finalHandler
	 */
	public function listen(callable $finalHandler = null)
	{
		$response = call_user_func($this->handler, $this->request, $this->response, $finalHandler);

		if (!$response instanceof ResponseInterface)
		{
			$response = $this->response;
		}

		$this->output->respond($response);
	}

	/**
	 * Method to get property Handler
	 *
	 * @return  callable
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * Method to set property handler
	 *
	 * @param   callable $handler
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setHandler($handler)
	{
		$this->handler = $handler;

		return $this;
	}

	/**
	 * Method to get property Request
	 *
	 * @return  ServerRequestInterface
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Method to set property request
	 *
	 * @param   ServerRequestInterface $request
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setRequest($request)
	{
		$this->request = $request;

		return $this;
	}

	/**
	 * Method to get property Output
	 *
	 * @return  OutputInterface
	 */
	public function getOutput()
	{
		if (!$this->output)
		{
			$this->output = new Output;
		}

		return $this->output;
	}

	/**
	 * Method to set property output
	 *
	 * @param   Output $output
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setOutput($output)
	{
		$this->output = $output;

		return $this;
	}
}
