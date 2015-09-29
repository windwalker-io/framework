<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Uri;

/**
 * Uri Helper
 *
 * This class provides an UTF-8 safe version of parse_url().
 *
 * This class is a fork from Joomla Uri.
 *
 * @since  2.0
 */
class UriHelper
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
	 * Build a query from a array (reverse of the PHP parse_str()).
	 *
	 * @param   array  $params  The array of key => value pairs to return as a query string.
	 *
	 * @return  string  The resulting query string.
	 *
	 * @see     parse_str()
	 * @since   2.0
	 */
	public static function buildQuery(array $params)
	{
		return urldecode(http_build_query($params, '', '&'));
	}

	/**
	 * Does a UTF-8 safe version of PHP parse_url function
	 *
	 * @param   string  $url  URL to parse
	 *
	 * @return  mixed  Associative array or false if badly formed URL.
	 *
	 * @see     http://us3.php.net/manual/en/function.parse-url.php
	 * @since   2.0
	 */
	public static function parseUrl($url)
	{
		$result = false;

		// Build arrays of values we need to decode before parsing
		$entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%24', '%2C', '%2F', '%3F', '%23', '%5B', '%5D');
		$replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "$", ",", "/", "?", "#", "[", "]");

		// Create encoded URL with special URL characters decoded so it can be parsed
		// All other characters will be encoded
		$encodedURL = str_replace($entities, $replacements, urlencode($url));

		// Parse the encoded URL
		$encodedParts = parse_url($encodedURL);

		// Now, decode each value of the resulting array
		if ($encodedParts)
		{
			foreach ($encodedParts as $key => $value)
			{
				$result[$key] = urldecode(str_replace($replacements, $entities, $value));
			}
		}

		return $result;
	}

	/**
	 * parseQuery
	 *
	 * @param   string  $query
	 *
	 * @return  mixed
	 */
	public static function parseQuery($query)
	{
		parse_str($query, $vars);

		return $vars;
	}

	/**
	 * filterScheme
	 *
	 * @param   string  $scheme
	 *
	 * @return  string
	 */
	public static function filterScheme($scheme)
	{
		$scheme = strtolower($scheme);
		$scheme = preg_replace('#:(//)?$#', '', $scheme);

		if (empty($scheme))
		{
			return '';
		}

		return $scheme;
	}

	/**
	 * Filter a query string to ensure it is propertly encoded.
	 *
	 * Ensures that the values in the query string are properly urlencoded.
	 *
	 * @param   string  $query
	 *
	 * @return  string
	 */
	public static function filterQuery($query)
	{
		if (! empty($query) && strpos($query, '?') === 0)
		{
			$query = substr($query, 1);
		}

		$parts = explode('&', $query);
		foreach ($parts as $index => $part)
		{
			list($key, $value) = static::splitQueryValue($part);

			if ($value === null)
			{
				$parts[$index] = static::filterQueryOrFragment($key);

				continue;
			}

			$parts[$index] = sprintf(
				'%s=%s',
				static::filterQueryOrFragment($key),
				static::filterQueryOrFragment($value)
			);
		}

		return implode('&', $parts);
	}

	/**
	 * Split a query value into a key/value tuple.
	 *
	 * @param   string  $value
	 *
	 * @return  array  A value with exactly two elements, key and value
	 */
	public static function splitQueryValue($value)
	{
		$data = explode('=', $value, 2);

		if (1 === count($data))
		{
			$data[] = null;
		}

		return $data;
	}

	/**
	 * Filter a query string key or value, or a fragment.
	 *
	 * @param   string  $value
	 *
	 * @return  string
	 */
	public static function filterQueryOrFragment($value)
	{
		return preg_replace_callback(
			'/(?:[^' . static::CHAR_UNRESERVED . static::CHAR_SUB_DELIMS . '%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
			function($matches)
			{
				return rawurlencode($matches[0]);
			},
			$value
		);
	}

	/**
	 * Filter a fragment value to ensure it is properly encoded.
	 *
	 * @param   string  $fragment
	 *
	 * @return  string
	 */
	public static function filterFragment($fragment)
	{
		if (null === $fragment)
		{
			$fragment = '';
		}

		if (! empty($fragment) && strpos($fragment, '#') === 0)
		{
			$fragment = substr($fragment, 1);
		}

		return static::filterQueryOrFragment($fragment);
	}

	/**
	 * Filters the path of a URI to ensure it is properly encoded.
	 *
	 * @param  string  $path
	 *
	 * @return  string
	 */
	public static function filterPath($path)
	{
		return preg_replace_callback(
			'/(?:[^' . self::CHAR_UNRESERVED . ':@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
			function($matches)
			{
				return rawurlencode($matches[0]);
			},
			$path
		);
	}

	/**
	 * Resolves //, ../ and ./ from a path and returns
	 * the result. Eg:
	 *
	 * /foo/bar/../boo.php	=> /foo/boo.php
	 * /foo/bar/../../boo.php => /boo.php
	 * /foo/bar/.././/boo.php => /foo/boo.php
	 *
	 * @param   string  $path  The URI path to clean.
	 *
	 * @return  string  Cleaned and resolved URI path.
	 *
	 * @since   2.0
	 */
	public static function cleanPath($path)
	{
		$path = explode('/', preg_replace('#(/+)#', '/', $path));

		for ($i = 0, $n = count($path); $i < $n; $i++)
		{
			if ($path[$i] == '.' || $path[$i] == '..')
			{
				if (($path[$i] == '.') || ($path[$i] == '..' && $i == 1 && $path[0] == ''))
				{
					unset($path[$i]);
					$path = array_values($path);
					$i--;
					$n--;
				}
				elseif ($path[$i] == '..' && ($i > 1 || ($i == 1 && $path[0] != '')))
				{
					unset($path[$i]);
					unset($path[$i - 1]);
					$path = array_values($path);
					$i -= 2;
					$n -= 2;
				}
			}
		}

		return implode('/', $path);
	}

	/**
	 * decode
	 *
	 * @param   string  $string
	 *
	 * @return  array|string
	 */
	public static function decode($string)
	{
		if (is_array($string))
		{
			foreach ($string as $k => $substring)
			{
				$string[$k] = static::decode($substring);
			}
		}
		else
		{
			$string = urldecode($string);
		}

		return $string;
	}

	/**
	 * encode
	 *
	 * @param   string  $string
	 *
	 * @return  array|string
	 */
	public static function encode($string)
	{
		if (is_array($string))
		{
			foreach ($string as $k => $substring)
			{
				$string[$k] = static::encode($substring);
			}
		}
		else
		{
			$string = urlencode($string);
		}

		return $string;
	}
}
