<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Uri;

use Psr\Http\Message\UriInterface as PsrUriInterface;

/**
 * The PsrUri class.
 * 
 * @since  2.1
 */
class PsrUri extends AbstractUri implements PsrUriInterface
{
	/**
	 * Sub-delimiters used in query strings and fragments.
	 *
	 * @const string
	 */
	const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';

	/**
	 * Unreserved characters used in paths, query strings, and fragments.
	 *
	 * @const string
	 */
	const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';

	/**
	 * Property standardSchemes.
	 *
	 * @var  integer[]
	 */
	protected $standardSchemes = array(
		'http'  => 80,
		'https' => 443
	);

	/**
	 * Constructor.
	 * You can pass a URI string to the constructor to initialise a specific URI.
	 *
	 * @param   string  $uri  The optional URI string
	 *
	 * @since   2.0
	 */
	public function __construct($uri = '')
	{
		if (!is_string($uri))
		{
			throw new \InvalidArgumentException('URI should be a string');
		}

		parent::__construct($uri);
	}

	/**
	 * Retrieve the authority component of the URI.
	 *
	 * If no authority information is present, this method MUST return an empty
	 * string.
	 *
	 * The authority syntax of the URI is:
	 *
	 * <pre>
	 * [user-info@]host[:port]
	 * </pre>
	 *
	 * If the port component is not set or is the standard port for the current
	 * scheme, it SHOULD NOT be included.
	 *
	 * @see https://tools.ietf.org/html/rfc3986#section-3.2
	 *
	 * @return  string  The URI authority, in "[user-info@]host[:port]" format.
	 */
	public function getAuthority()
	{
		if (empty($this->host))
		{
			return '';
		}

		$authority = $this->host;

		$userInfo = $this->getUserInfo();

		if ($userInfo)
		{
			$authority = $userInfo . '@' . $authority;
		}

		if (!$this->isStandardPort($this->scheme, $this->host, $this->port))
		{
			$authority .= ':' . $this->port;
		}

		return $authority;
	}

	/**
	 * Return an instance with the specified scheme.
	 *
	 * This method MUST retain the state of the current instance, and return
	 * an instance that contains the specified scheme.
	 *
	 * Implementations MUST support the schemes "http" and "https" case
	 * insensitively, and MAY accommodate other schemes if required.
	 *
	 * An empty scheme is equivalent to removing the scheme.
	 *
	 * @param   string  $scheme  The scheme to use with the new instance.
	 *
	 * @return  static  A new instance with the specified scheme.
	 *
	 * @throws  \InvalidArgumentException for invalid or unsupported schemes.
	 */
	public function withScheme($scheme)
	{
		if (!is_string($scheme))
		{
			throw new \InvalidArgumentException('URI Scheme should be a string.');
		}

		$scheme = UriHelper::filterScheme($scheme);

		$new = clone $this;
		$new->scheme = $scheme;

		return $new;
	}

	/**
	 * Return an instance with the specified user information.
	 *
	 * This method MUST retain the state of the current instance, and return
	 * an instance that contains the specified user information.
	 *
	 * Password is optional, but the user information MUST include the
	 * user; an empty string for the user is equivalent to removing user
	 * information.
	 *
	 * @param  string  $user      The user name to use for authority.
	 * @param  string  $password  The password associated with $user.
	 *
	 * @return  static  A new instance with the specified user information.
	 */
	public function withUserInfo($user, $password = null)
	{
		if (!is_string($user))
		{
			throw new \InvalidArgumentException('URI User should be a string.');
		}

		if ($password !== null && !is_string($password))
		{
			throw new \InvalidArgumentException('URI Password should be a string or NULL.');
		}

		$new = clone $this;
		$new->user = $user;
		$new->pass = $password;

		return $new;
	}

	/**
	 * Return an instance with the specified host.
	 *
	 * This method MUST retain the state of the current instance, and return
	 * an instance that contains the specified host.
	 *
	 * An empty host value is equivalent to removing the host.
	 *
	 * @param   string  $host  The hostname to use with the new instance.
	 *
	 * @return  static  A new instance with the specified host.
	 *
	 * @throws  \InvalidArgumentException for invalid hostnames.
	 */
	public function withHost($host)
	{
		if (!is_string($host))
		{
			throw new \InvalidArgumentException('URI Host should be a string.');
		}

		$new = clone $this;
		$new->host = $host;

		return $new;
	}

	/**
	 * Return an instance with the specified port.
	 *
	 * This method MUST retain the state of the current instance, and return
	 * an instance that contains the specified port.
	 *
	 * Implementations MUST raise an exception for ports outside the
	 * established TCP and UDP port ranges.
	 *
	 * A null value provided for the port is equivalent to removing the port
	 * information.
	 *
	 * @param   int  $port  The port to use with the new instance; a null value
	 *                      removes the port information.
	 *
	 * @return  static  A new instance with the specified port.
	 * @throws  \InvalidArgumentException for invalid ports.
	 */
	public function withPort($port)
	{
		if (is_object($port) || is_array($port))
		{
			throw new \InvalidArgumentException('Invalid port type.');
		}

		if ($port !== null)
		{
			$port = (int) $port;

			if ($port < 1 || $port > 65535)
			{
				throw new \InvalidArgumentException(sprintf('Number of "%d" is not a valid TCP/UDP port', $port));
			}
		}

		$new = clone $this;
		$new->port = $port;

		return $new;
	}

	/**
	 * Return an instance with the specified path.
	 *
	 * This method MUST retain the state of the current instance, and return
	 * an instance that contains the specified path.
	 *
	 * The path can either be empty or absolute (starting with a slash) or
	 * rootless (not starting with a slash). Implementations MUST support all
	 * three syntaxes.
	 *
	 * If the path is intended to be domain-relative rather than path relative then
	 * it must begin with a slash ("/"). Paths not starting with a slash ("/")
	 * are assumed to be relative to some base path known to the application or
	 * consumer.
	 *
	 * Users can provide both encoded and decoded path characters.
	 * Implementations ensure the correct encoding as outlined in getPath().
	 *
	 * @param   string  $path  The path to use with the new instance.
	 *
	 * @return  static  A new instance with the specified path.
	 * @throws  \InvalidArgumentException for invalid paths.
	 */
	public function withPath($path)
	{
		if (!is_string($path))
		{
			throw new \InvalidArgumentException('URI Path should be a string.');
		}

		$path = (string) $path;

		if (strpos($path, '?') !== false || strpos($path, '#') !== false )
		{
			throw new \InvalidArgumentException('Path should not contain `?` and `#` symbols.');
		}

		$path = UriHelper::cleanPath($path);
		$path = UriHelper::filterPath($path);

		$new = clone $this;
		$new->path = $path;

		return $new;
	}

	/**
	 * Return an instance with the specified query string.
	 *
	 * This method MUST retain the state of the current instance, and return
	 * an instance that contains the specified query string.
	 *
	 * Users can provide both encoded and decoded query characters.
	 * Implementations ensure the correct encoding as outlined in getQuery().
	 *
	 * An empty query string value is equivalent to removing the query string.
	 *
	 * @param  string|array  $query  The query string to use with the new instance.
	 *
	 * @return  static  A new instance with the specified query string.
	 * @throws  \InvalidArgumentException for invalid query strings.
	 */
	public function withQuery($query)
	{
		if (!is_string($query))
		{
			throw new \InvalidArgumentException('URI Query should be a string.');
		}

		$query = UriHelper::filterQuery($query);

		$new = clone $this;
		$new->vars = UriHelper::parseQuery($query);
		$new->query = $query;

		return $new;
	}

	/**
	 * Return an instance with the specified URI fragment.
	 *
	 * This method MUST retain the state of the current instance, and return
	 * an instance that contains the specified URI fragment.
	 *
	 * Users can provide both encoded and decoded fragment characters.
	 * Implementations ensure the correct encoding as outlined in getFragment().
	 *
	 * An empty fragment value is equivalent to removing the fragment.
	 *
	 * @param   string  $fragment  The fragment to use with the new instance.
	 *
	 * @return  static  A new instance with the specified fragment.
	 */
	public function withFragment($fragment)
	{
		if (!is_string($fragment))
		{
			throw new \InvalidArgumentException('URI Fragment should be a string.');
		}

		$fragment = UriHelper::filterFragment($fragment);

		$new = clone $this;
		$new->fragment = $fragment;

		return $new;
	}

	/**
	 * Is a given port non-standard for the current scheme?
	 *
	 * @param  string  $scheme
	 * @param  string  $host
	 * @param  int     $port
	 *
	 * @return  boolean
	 */
	protected function isStandardPort($scheme, $host, $port)
	{
		if (!$scheme)
		{
			return false;
		}

		if (!$host || !$port)
		{
			return true;
		}

		return (isset($this->standardSchemes[$scheme]) && $port == $this->standardSchemes[$scheme]);
	}
}
