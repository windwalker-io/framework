<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Windwalker\Http\Output\Output;
use Windwalker\Http\Output\OutputInterface;
use Windwalker\Http\Request\ServerRequestFactory;
use Windwalker\Http\Response\Response;

/**
 * The Server class.
 *
 * @since  3.0
 */
class HttpServer
{
	/**
	 * The callback to handle server task.
	 *
	 * @var  callable
	 */
	protected $handler;

	/**
	 * Request object to contain request data.
	 *
	 * @var  ServerRequestInterface
	 */
	protected $request;

	/**
	 * The response object to wrap body and headers.
	 *
	 * @var  ResponseInterface
	 */
	protected $response;

	/**
	 * The output emitter.
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
	 * @param   callable  $handler   The server handler.
	 * @param   array     $server    The server variable. Typically this will be the $_SERVER superglobal.
	 * @param   array     $query     The GET uri query. Typically this will be the $_GET superglobal.
	 * @param   array     $body      The POST body. Typically this will be the $_POST superglobal.
	 * @param   array     $cookies   The cookies. Typically this will be the $_COOKIE superglobal
	 * @param   array     $files     The uploaded file data. Typically this will be the $_FILES superglobal.
	 * 
	 * @return  static  The Server instance.
	 */
	public static function createFromGlobals($handler = null, array $server = array(), array $query = array(), array $body = array(), array $cookies = array(), array $files = array())
	{
		$request  = ServerRequestFactory::createFromGlobals($server, $query, $body, $cookies, $files);

		return new static($handler, $request);
	}

	/**
	 * Create a Server instance from an existing request object
	 *
	 * Provided a callback, an existing request object, and optionally an
	 * existing response object, create and return the Server instance.
	 *
	 * If no Response object is provided, one will be created.
	 *
	 * @param   callable                $handler   The server handler.
	 * @param   ServerRequestInterface  $request   The Request object.
	 * @param   ResponseInterface       $response  The Response object.
	 * @param   OutputInterface         $output    The Output emitter object.
	 *
	 * @return  static  The Server instance.
	 */
	public static function create($handler = null, ServerRequestInterface $request, ResponseInterface $response = null, OutputInterface $output = null)
	{
		return new static($handler, $request, $response, $output);
	}

	/**
	 * Create a Server instance from an existing request object
	 *
	 * Provided a callback, an existing request object, and optionally an
	 * existing response object, create and return the Server instance.
	 *
	 * If no Response object is provided, one will be created.
	 *
	 * @param   callable                $handler   The server handler.
	 * @param   ServerRequestInterface  $request   The Request object.
	 * @param   ResponseInterface       $response  The Response object.
	 * @param   OutputInterface         $output    The Output emitter object.
	 */
	public function __construct($handler = null, ServerRequestInterface $request, ResponseInterface $response = null, OutputInterface $output = null)
	{
		$this->handler  = $handler;
		$this->request  = $request;
		$this->response = $response ? : new Response;
		$this->output   = $output ? : $this->getOutput();
	}

	/**
	 * Execute the server.
	 *
	 * @param   callable $errorHandler The error handler callback.
	 */
	public function listen($errorHandler = null)
	{
		if (version_compare(PHP_VERSION, '5.4', '>=') && $this->handler instanceof \Closure)
		{
			$this->handler = $this->handler->bindTo($this);
		}

		$response = call_user_func($this->handler, $this->request, $this->response, $errorHandler);

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

	/**
	 * Method to get property Response
	 *
	 * @return  ResponseInterface
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Method to set property response
	 *
	 * @param   ResponseInterface $response
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setResponse($response)
	{
		$this->response = $response;

		return $this;
	}
}
