<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface HttpClientInterface
 *
 * @since  2.1
 */
interface HttpClientInterface
{
	/**
	 * Request a remote server.
	 *
	 * This method will build a Request object and use send() method to send request.
	 *
	 * @param string        $method   The method type.
	 * @param string|object $url      The URL to request, may be string or Uri object.
	 * @param mixed         $data     The request body data, can be an array of POST data.
	 * @param array         $headers  The headers array.
	 *
	 * @return  ResponseInterface
	 */
	public function request($method, $url, $data = null, $headers);

	/**
	 * Send a request to remote.
	 *
	 * @param   RequestInterface  $request  The Psr Request object.
	 *
	 * @return  ResponseInterface
	 */
	public function send(RequestInterface $request);
}
