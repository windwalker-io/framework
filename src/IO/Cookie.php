<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\IO;

/**
 * Windwalker Input Cookie Class
 *
 * @since  2.0
 */
class Cookie extends Input
{
	/**
	 * Prepare source.
	 *
	 * @param   array    $source     Optional source data. If omitted, a copy of the server variable '_REQUEST' is used.
	 * @param   boolean  $reference  If set to true, he source in first argument will be reference.
	 *
	 * @return  void
	 */
	public function prepareSource(&$source = null, $reference = false)
	{
		$this->data = &$_COOKIE;
	}

	/**
	 * Sets a value
	 *
	 * @param   string   $name      Name of the value to set.
	 * @param   mixed    $value     Value to assign to the input.
	 * @param   integer  $expire    The time the cookie expires. This is a Unix timestamp so is in number
	 *                              of seconds since the epoch. In other words, you'll most likely set this
	 *                              with the time() function plus the number of seconds before you want it
	 *                              to expire. Or you might use mktime(). time()+60*60*24*30 will set the
	 *                              cookie to expire in 30 days. If set to 0, or omitted, the cookie will
	 *                              expire at the end of the session (when the browser closes).
	 * @param   string   $path      The path on the server in which the cookie will be available on. If set
	 *                              to '/', the cookie will be available within the entire domain. If set to
	 *                              '/foo/', the cookie will only be available within the /foo/ directory and
	 *                              all sub-directories such as /foo/bar/ of domain. The default value is the
	 *                              current directory that the cookie is being set in.
	 * @param   string   $domain    The domain that the cookie is available to. To make the cookie available
	 *                              on all subdomains of example.com (including example.com itself) then you'd
	 *                              set it to '.example.com'. Although some browsers will accept cookies without
	 *                              the initial ., RFC 2109 requires it to be included. Setting the domain to
	 *                              'www.example.com' or '.www.example.com' will make the cookie only available
	 *                              in the www subdomain.
	 * @param   boolean  $secure    Indicates that the cookie should only be transmitted over a secure HTTPS
	 *                              connection from the client. When set to TRUE, the cookie will only be set
	 *                              if a secure connection exists. On the server-side, it's on the programmer
	 *                              to send this kind of cookie only on secure connection (e.g. with respect
	 *                              to $_SERVER["HTTPS"]).
	 * @param   boolean  $httpOnly  When TRUE the cookie will be made accessible only through the HTTP protocol.
	 *                              This means that the cookie won't be accessible by scripting languages, such
	 *                              as JavaScript. This setting can effectively help to reduce identity theft
	 *                              through XSS attacks (although it is not supported by all browsers).
	 *
	 * @return  void
	 *
	 * @link    http://www.ietf.org/rfc/rfc2109.txt
	 * @see     setcookie()
	 * @since   2.0
	 */
	public function set($name, $value, $expire = 0, $path = '', $domain = '', $secure = false, $httpOnly = false)
	{
		setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);

		$this->data[$name] = $value;
	}
}
