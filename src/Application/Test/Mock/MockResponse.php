<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Application\Test\Mock;

use Windwalker\Application\Web\Response;

/**
 * The MockResponse class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class MockResponse extends Response
{
	/**
	 * Property sentHeaders.
	 *
	 * @var  string[]
	 */
	public $sentHeaders = array();

	/**
	 * Property headers.
	 *
	 * @var  array
	 */
	public $headers = array();

	/**
	 * Method to send a header to the client.  We are wrapping this to isolate the header() function
	 * from our code base for testing reasons.
	 *
	 * @param   string   $string   The header string.
	 * @param   boolean  $replace  The optional replace parameter indicates whether the header should
	 *                             replace a previous similar header, or add a second header of the same type.
	 * @param   integer  $code     Forces the HTTP response code to the specified value. Note that
	 *                             this parameter only has an effect if the string is not empty.
	 *
	 * @return  static
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function header($string, $replace = true, $code = null)
	{
		$this->sentHeaders[] = $string;

		return $this;
	}

	/**
	 * Method to check to see if headers have already been sent.  We are wrapping this to isolate the
	 * headers_sent() function from our code base for testing reasons.
	 *
	 * @return  boolean  True if the headers have already been sent.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function checkHeadersSent()
	{
		return false;
	}

	/**
	 * Method to check the current client connection status to ensure that it is alive.  We are
	 * wrapping this to isolate the connection_status() function from our code base for testing reasons.
	 *
	 * @return  boolean  True if the connection is valid and normal.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function checkConnectionAlive()
	{
		return (connection_status() === CONNECTION_NORMAL);
	}
}
