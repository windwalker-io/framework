<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Uri;

/**
 * Uri Class
 *
 * Abstract base for out uri classes.
 *
 * This class should be considered an implementation detail. Typehint against UriInterface.
 *
 * @since  2.0
 */
abstract class AbstractUri implements UriInterface
{
	const SCHEME_HTTP = 'http';
	const SCHEME_HTTPS = 'https';

	/**
	 * @var    string  Original URI
	 * @since  2.0
	 */
	protected $uri = null;

	/**
	 * @var    string  Protocol
	 * @since  2.0
	 */
	protected $scheme = null;

	/**
	 * @var    string  Host
	 * @since  2.0
	 */
	protected $host = null;

	/**
	 * @var    integer  Port
	 * @since  2.0
	 */
	protected $port = null;

	/**
	 * @var    string  Username
	 * @since  2.0
	 */
	protected $user = null;

	/**
	 * @var    string  Password
	 * @since  2.0
	 */
	protected $pass = null;

	/**
	 * @var    string  Path
	 * @since  2.0
	 */
	protected $path = null;

	/**
	 * @var    string  Query
	 * @since  2.0
	 */
	protected $query = null;

	/**
	 * @var    string  Anchor
	 * @since  2.0
	 */
	protected $fragment = null;

	/**
	 * @var    array  Query variable hash
	 * @since  2.0
	 */
	protected $vars = array();

	/**
	 * Constructor.
	 * You can pass a URI string to the constructor to initialise a specific URI.
	 *
	 * @param   string  $uri  The optional URI string
	 *
	 * @since   2.0
	 */
	public function __construct($uri = null)
	{
		if (!is_null($uri))
		{
			$this->parse($uri);
		}
	}

	/**
	 * Magic method to get the string representation of the URI object.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Returns full uri string.
	 *
	 * @param   array  $parts  An array specifying the parts to render.
	 *
	 * @return  string  The rendered URI string.
	 *
	 * @since   2.0
	 */
	public function toString(array $parts = array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'))
	{
		// Make sure the query is created
		$query = $this->getQuery();

		$uri = '';
		$uri .= in_array('scheme', $parts) ? (!empty($this->scheme) ? $this->scheme . '://' : '') : '';
		$uri .= in_array('user', $parts) ? $this->user : '';
		$uri .= in_array('pass', $parts) ? (!empty($this->pass) ? ':' : '') . $this->pass . (!empty($this->user) ? '@' : '') : '';
		$uri .= in_array('host', $parts) ? $this->host : '';
		$uri .= in_array('port', $parts) ? (!empty($this->port) ? ':' : '') . $this->port : '';
		$uri .= in_array('path', $parts) ? $this->path ? '/' . ltrim($this->path, '/') : '' : '';
		$uri .= in_array('query', $parts) ? (!empty($query) ? '?' . $query : '') : '';
		$uri .= in_array('fragment', $parts) ? (!empty($this->fragment) ? '#' . $this->fragment : '') : '';

		return $uri;
	}

	/**
	 * Checks if variable exists.
	 *
	 * @param   string  $name  Name of the query variable to check.
	 *
	 * @return  boolean  True if the variable exists.
	 *
	 * @since   2.0
	 */
	public function hasVar($name)
	{
		return array_key_exists($name, $this->vars);
	}

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
	public function getVar($name, $default = null)
	{
		if (array_key_exists($name, $this->vars))
		{
			return $this->vars[$name];
		}

		return $default;
	}

	/**
	 * Returns flat query string.
	 *
	 * @param   boolean  $toArray  True to return the query as a key => value pair array.
	 *
	 * @return  string   Query string.
	 *
	 * @since   2.0
	 */
	public function getQuery($toArray = false)
	{
		if ($toArray)
		{
			return $this->vars;
		}

		// If the query is empty build it first
		if (is_null($this->query))
		{
			$this->query = UriHelper::buildQuery($this->vars);
		}

		return $this->query;
	}

	/**
	 * Get URI scheme (protocol)
	 * ie. http, https, ftp, etc...
	 *
	 * @return  string  The URI scheme.
	 *
	 * @since   2.0
	 */
	public function getScheme()
	{
		return $this->scheme;
	}

	/**
	 * Get URI username
	 * Returns the username, or null if no username was specified.
	 *
	 * @return  string  The URI username.
	 *
	 * @since   2.0
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Get URI password
	 * Returns the password, or null if no password was specified.
	 *
	 * @return  string  The URI password.
	 *
	 * @since   2.0
	 */
	public function getPass()
	{
		return $this->pass;
	}

	/**
	 * Retrieve the user information component of the URI.
	 *
	 * If no user information is present, this method MUST return an empty
	 * string.
	 *
	 * If a user is present in the URI, this will return that value;
	 * additionally, if the password is also present, it will be appended to the
	 * user value, with a colon (":") separating the values.
	 *
	 * The trailing "@" character is not part of the user information and MUST
	 * NOT be added.
	 *
	 * @return  string  The URI user information, in "username[:password]" format.
	 *
	 * @since   2.1
	 */
	public function getUserInfo()
	{
		$info = $this->user;

		if ($info && $this->pass)
		{
			$info .= ':' . $this->pass;
		}

		return $info;
	}

	/**
	 * Get URI host
	 * Returns the hostname/ip or null if no hostname/ip was specified.
	 *
	 * @return  string  The URI host.
	 *
	 * @since   2.0
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Get URI port
	 * Returns the port number, or null if no port was specified.
	 *
	 * @return  integer  The URI port number.
	 *
	 * @since   2.0
	 */
	public function getPort()
	{
		return (isset($this->port)) ? $this->port : null;
	}

	/**
	 * Gets the URI path string.
	 *
	 * @return  string  The URI path string.
	 *
	 * @since   2.0
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Get the URI archor string
	 * Everything after the "#".
	 *
	 * @return  string  The URI anchor string.
	 *
	 * @since   2.0
	 */
	public function getFragment()
	{
		return $this->fragment;
	}

	/**
	 * Checks whether the current URI is using HTTPS.
	 *
	 * @return  boolean  True if using SSL via HTTPS.
	 *
	 * @since   2.0
	 */
	public function isSSL()
	{
		return $this->getScheme() == 'https' ? true : false;
	}



	/**
	 * Parse a given URI and populate the class fields.
	 *
	 * @param   string  $uri  The URI string to parse.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.0
	 */
	protected function parse($uri)
	{
		// Set the original URI to fall back on
		$this->uri = $uri;

		/*
		 * Parse the URI and populate the object fields. If URI is parsed properly,
		 * set method return value to true.
		 */

		$parts = UriHelper::parseUrl($uri);

		$retval = ($parts) ? true : false;

		// We need to replace &amp; with & for parse_str to work right...
		if (isset($parts['query']) && strpos($parts['query'], '&amp;'))
		{
			$parts['query'] = str_replace('&amp;', '&', $parts['query']);
		}

		$this->scheme   = isset($parts['scheme']) ? $parts['scheme'] : null;
		$this->user     = isset($parts['user']) ? $parts['user'] : null;
		$this->pass     = isset($parts['pass']) ? $parts['pass'] : null;
		$this->host     = isset($parts['host']) ? $parts['host'] : null;
		$this->port     = isset($parts['port']) ? $parts['port'] : null;
		$this->path     = isset($parts['path']) ? $parts['path'] : null;
		$this->query    = isset($parts['query']) ? $parts['query'] : null;
		$this->fragment = isset($parts['fragment']) ? $parts['fragment'] : null;

		// Parse the query
		if (isset($parts['query']))
		{
			$this->vars = UriHelper::parseQuery($parts['query']);
		}

		return $retval;
	}

	/**
	 * getUri
	 *
	 * @return  string
	 */
	public function getOriginal()
	{
		return $this->uri;
	}
}
