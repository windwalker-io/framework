<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Http\Test\Mock;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Windwalker\Http\Response;
use Windwalker\Http\Transport\AbstractTransport;

/**
 * The MockTransport class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class MockTransport extends AbstractTransport
{
	/**
	 * Property request.
	 *
	 * @var  RequestInterface
	 */
	public $request;

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
		$this->request = $request;

		return $this->doRequest($request);
	}

	/**
	 * Send a request to the server and return a Response object with the response.
	 *
	 * @param   RequestInterface $request The request object to store request params.
	 *
	 * @return  ResponseInterface
	 *
	 * @since   2.1
	 */
	protected function doRequest(RequestInterface $request)
	{
		return new Response;
	}

	/**
	 * Method to check if HTTP transport layer available for using
	 *
	 * @return  boolean  True if available else false
	 *
	 * @since   2.1
	 */
	public static function isSupported()
	{
		return true;
	}
}
