<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http;

use Psr\Http\Message\RequestInterface;
use Windwalker\Application\Web\Output;

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
	 * @var  RequestInterface
	 */
	protected $request;

	/**
	 * Property emitter.
	 *
	 * @var  Output
	 */
	protected $output;

	/**
	 * Server constructor.
	 *
	 * @param callable         $handler
	 * @param RequestInterface $request
	 * @param Output           $output
	 */
	public function __construct(callable $handler, RequestInterface $request, Output $output)
	{
		$this->handler = $handler;
		$this->request = $request;
		$this->output  = $output;
	}

	/**
	 * listen
	 *
	 * @param callable $finalHandler
	 *
	 * @return  void
	 */
	public function listen(callable $finalHandler = null)
	{
		$response = call_user_func($this->handler, $this->request, $this->output, $finalHandler);

		$this->output->setBody($response);

		$this->output->respond();
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
	 * @return  RequestInterface
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Method to set property request
	 *
	 * @param   RequestInterface $request
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
	 * @return  Output
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
