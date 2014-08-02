<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Application\Web;

use Windwalker\Uri\Uri;

/**
 * Interface WebClientInterface
 */
interface WebClientInterface
{
	/**
	 * getSystemUri
	 *
	 * @param string $requestUri
	 *
	 * @return  Uri
	 */
	public function getSystemUri($requestUri = null);

	/**
	 * getPlatform
	 *
	 * @return  int
	 */
	public function getPlatform();

	/**
	 * getMobile
	 *
	 * @return  boolean
	 */
	public function getMobile();

	/**
	 * getEngine
	 *
	 * @return  int
	 */
	public function getEngine();

	/**
	 * getBrowser
	 *
	 * @return  int
	 */
	public function getBrowser();

	/**
	 * getBrowserVersion
	 *
	 * @return  string
	 */
	public function getBrowserVersion();

	/**
	 * getLanguages
	 *
	 * @return  array
	 */
	public function getLanguages();

	/**
	 * getEncodings
	 *
	 * @return  array
	 */
	public function getEncodings();

	/**
	 * getUserAgent
	 *
	 * @return  string
	 */
	public function getUserAgent();

	/**
	 * setUserAgent
	 *
	 * @param   string $userAgent
	 *
	 * @return  WebClient  Return self to support chaining.
	 */
	public function setUserAgent($userAgent);

	/**
	 * getRobot
	 *
	 * @return  boolean
	 */
	public function isRobot();

	/**
	 * Determine if we are using a secure (SSL) connection.
	 *
	 * @return  boolean  True if using SSL, false if not.
	 *
	 * @since   1.0
	 */
	public function isSSLConnection();
}

