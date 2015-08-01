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
 * The TransportInterface class.
 * 
 * @since  {DEPLOY_VERSION}
 */
interface TransportInterface
{
	/**
	 * Constructor.
	 *
	 * @param   array|\ArrayAccess  $options  Client options object.
	 *
	 * @since   1.0
	 */
	public function __construct($options = array());

	/**
	 * Send a request to the server and return a Response object with the response.
	 *
	 * @param  RequestInterface  $request  The request object to send.
	 *
	 * @return ResponseInterface
	 * @since   1.0
	 */
	public function request(RequestInterface $request);

	/**
	 * Method to check if HTTP transport layer available for using
	 *
	 * @return  boolean  True if available else false
	 *
	 * @since   1.0
	 */
	public static function isSupported();
}
