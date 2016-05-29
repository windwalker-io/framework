<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Http\Helper;

/**
 * The HttpSecurityHelper class.
 *
 * A simple helper class based on Phly HeaderSecurity class.
 * 
 * @since  2.1
 */
abstract class HeaderHelper
{
	/**
	 * Search for a header value.
	 *
	 * Does a case-insensitive search for a matching header.
	 *
	 * If found, it is returned as a string, using comma concatenation.
	 *
	 * If not, the $default is returned.
	 *
	 * @param array  $headers
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return string
	 */
	public static function getValue(array $headers, $name, $default = null)
	{
		$name    = strtolower($name);
		$headers = array_change_key_case($headers, CASE_LOWER);

		if (array_key_exists($name, $headers))
		{
			$value = is_array($headers[$name]) ? implode(', ', $headers[$name]) : $headers[$name];

			return $value;
		}

		return $default;
	}

	/**
	 * Check whether or not a header name is valid.
	 *
	 * @param   mixed  $name
	 *
	 * @return  boolean
	 *
	 * @see http://tools.ietf.org/html/rfc7230#section-3.2
	 */
	public static function isValidName($name)
	{
		return preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $name);
	}

	/**
	 * Method to remove invalid CRLF injection from header value.
	 * 
	 * Follows RFC-7230, only allows visible ASCII characters, spaces
	 * and tabs in header value. every new line must only contains
	 * a single CRLF and a space or tab after it.
	 *
	 * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
	 * @see https://tools.ietf.org/html/rfc7230
	 *
	 * @param   string  $value
	 *
	 * @return  string
	 */
	public static function filter($value)
	{
		$value  = (string) $value;
		$length = strlen($value);
		$string = '';

		for ($i = 0; $i < $length; $i += 1)
		{
			$ascii = ord($value[$i]);

			// Detect continuation sequences
			if ($ascii === 13)
			{
				$lf = ord($value[$i + 1]);
				$ws = ord($value[$i + 2]);

				if ($lf === 10 && in_array($ws, array(9, 32), true))
				{
					$string .= $value[$i] . $value[$i + 1];
					$i += 1;
				}

				continue;
			}

			// Non-visible, non-whitespace characters
			// 9 === horizontal tab
			// 32-126, 128-254 === visible
			// 127 === DEL
			// 255 === null byte
			if (($ascii < 32 && $ascii !== 9) || $ascii === 127 || $ascii > 254)
			{
				continue;
			}

			$string .= $value[$i];
		}

		return $string;
	}

	/**
	 * Method to validate a header value.
	 *
	 * Follows RFC-7230, only allows visible ASCII characters, spaces
	 * and tabs in header value. every new line must only contains
	 * a single CRLF and a space or tab after it.
	 *
	 * @return  boolean
	 *
	 * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
	 * @see https://tools.ietf.org/html/rfc7230
	 */
	public static function isValidValue($value)
	{
		$value  = (string) $value;

		// Look for:
		// \n not preceded by \r, OR
		// \r not followed by \n, OR
		// \r\n not followed by space or horizontal tab; these are all CRLF attacks
		if (preg_match("#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#", $value))
		{
			return false;
		}

		$length = strlen($value);

		for ($i = 0; $i < $length; $i += 1)
		{
			$ascii = ord($value[$i]);

			// Non-visible, non-whitespace characters
			// 9 === horizontal tab
			// 10 === line feed
			// 13 === carriage return
			// 32-126, 128-254 === visible
			// 127 === DEL
			// 255 === null byte
			if (($ascii < 32 && ! in_array($ascii, array(9, 10, 13), true)) || $ascii === 127 || $ascii > 254)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * isValidProtocolVersion
	 *
	 * @param   string  $version
	 *
	 * @return  boolean
	 */
	public static function isValidProtocolVersion($version)
	{
		if (!is_string($version) || empty($version))
		{
			return false;
		}

		return (bool) preg_match('#^(1\.[01]|2)$#', $version);
	}

	/**
	 * allToArray
	 *
	 * @param mixed $value
	 *
	 * @return  array
	 */
	public static function allToArray($value)
	{
		if ($value instanceof \Traversable)
		{
			$value = iterator_to_array($value);
		}

		if (is_object($value))
		{
			$value = get_object_vars($value);
		}

		$value = (array) $value;

		foreach ($value as $k => $v)
		{
			if (!static::isValidValue($v))
			{
				throw new \InvalidArgumentException('Value :' . $value . ' is invalid.');
			}
		}

		return $value;
	}

	/**
	 * arrayOnlyContainsString
	 *
	 * @param array $array
	 *
	 * @return  bool
	 */
	public static function arrayOnlyContainsString(array $array)
	{
		foreach ($array as $value)
		{
			if (is_numeric($value))
			{
				$value = (string) $value;
			}

			if (!is_string($value))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * toHeaderLine
	 *
	 * @param array  $headers
	 * @param bool   $toString
	 *
	 * @return  array|string
	 */
	public static function toHeaderLine($headers, $toString = false)
	{
		$headerArray = array();

		foreach ($headers as $key => $value)
		{
			$value = is_array($value) ? implode(',', $value) : $value;

			$headerArray[] = static::normalizeHeaderName($key) . ': ' . $value;
		}

		if ($toString)
		{
			$headerArray = implode($headerArray, "\r\n");
		}

		return $headerArray;
	}

	/**
	 * Filter a header name to wordcase
	 *
	 * @param string $header
	 *
	 * @return string
	 */
	public static function normalizeHeaderName($header)
	{
		$filtered = str_replace('-', ' ', $header);
		$filtered = ucwords($filtered);

		return str_replace(' ', '-', $filtered);
	}
}
