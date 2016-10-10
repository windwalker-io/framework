<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Http\Client;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * The HttpPlugClientInterface class.
 *
 * @since  3.0.1
 */
interface HttpClient
{
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
	public function sendRequest(RequestInterface $request);
}
