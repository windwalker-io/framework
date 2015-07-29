<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Uri;

/**
 * Uri Interface
 *
 * Interface for read-only access to Uris. This class is a fork from Joomla Uri.
 *
 * @since  2.0
 */
interface UriInterface
{
	/**
	 * Magic method to get the string representation of the URI object.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function __toString();

	/**
	 * Returns full uri string.
	 *
	 * @param   array  $parts  An array specifying the parts to render.
	 *
	 * @return  string  The rendered URI string.
	 *
	 * @since   2.0
	 */
	public function toString(array $parts = array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'));

	/**
	 * Checks if variable exists.
	 *
	 * @param   string  $name  Name of the query variable to check.
	 *
	 * @return  boolean  True if the variable exists.
	 *
	 * @since   2.0
	 */
	public function hasVar($name);

	/**
	 * Returns a query variable by name.
	 *
	 * @param   string  $name     Name of the query variable to get.
	 * @param   string  $default  Default value to return if the variable is not set.
	 *
	 * @return  array   Query variables.
	 *
	 * @since   2.0
	 */
	public function getVar($name, $default = null);

	/**
	 * Returns flat query string.
	 *
	 * @param   boolean  $toArray  True to return the query as a key => value pair array.
	 *
	 * @return  string   Query string.
	 *
	 * @since   2.0
	 */
	public function getQuery($toArray = false);

	/**
	 * Get URI scheme (protocol)
	 * ie. http, https, ftp, etc...
	 *
	 * @return  string  The URI scheme.
	 *
	 * @since   2.0
	 */
	public function getScheme();

	/**
	 * Get URI username
	 * Returns the username, or null if no username was specified.
	 *
	 * @return  string  The URI username.
	 *
	 * @since   2.0
	 */
	public function getUser();

	/**
	 * Get URI password
	 * Returns the password, or null if no password was specified.
	 *
	 * @return  string  The URI password.
	 *
	 * @since   2.0
	 */
	public function getPass();

	/**
	 * Get URI host
	 * Returns the hostname/ip or null if no hostname/ip was specified.
	 *
	 * @return  string  The URI host.
	 *
	 * @since   2.0
	 */
	public function getHost();

	/**
	 * Get URI port
	 * Returns the port number, or null if no port was specified.
	 *
	 * @return  integer  The URI port number.
	 *
	 * @since   2.0
	 */
	public function getPort();

	/**
	 * Gets the URI path string.
	 *
	 * @return  string  The URI path string.
	 *
	 * @since   2.0
	 */
	public function getPath();

	/**
	 * Get the URI archor string
	 * Everything after the "#".
	 *
	 * @return  string  The URI anchor string.
	 *
	 * @since   2.0
	 */
	public function getFragment();

	/**
	 * Checks whether the current URI is using HTTPS.
	 *
	 * @return  boolean  True if using SSL via HTTPS.
	 *
	 * @since   2.0
	 */
	public function isSSL();
}
