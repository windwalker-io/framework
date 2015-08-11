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
use Psr\Http\Message\StreamInterface;

/**
 * The TransportInterface class.
 * 
 * @since  2.1
 */
interface TransportInterface
{
	/**
	 * Constructor.
	 *
	 * @param   array|\ArrayAccess  $options  Client options object.
	 *
	 * @since   2.1
	 */
	public function __construct($options = array());

	/**
	 * Send a request to the server and return a Response object with the response.
	 *
	 * @param   RequestInterface  $request  The request object to store request params.
	 *
	 * @return  ResponseInterface
	 *
	 * @since   2.1
	 */
	public function request(RequestInterface $request);

	/**
	 * Use stream to download file.
	 *
	 * @param   RequestInterface        $request The request object to store request params.
	 * @param   string|StreamInterface  $dest    The dest path to store file.
	 *
	 * @return  ResponseInterface
	 * @since   2.1
	 */
	public function download(RequestInterface $request, $dest);

	/**
	 * Method to check if HTTP transport layer available for using
	 *
	 * @return  boolean  True if available else false
	 *
	 * @since   2.1
	 */
	public static function isSupported();
}
