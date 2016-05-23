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
 * A simple helper class based on Phly HeaderSecurity.
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
	 * Filter a header value
	 *
	 * Ensures CRLF header injection vectors are filtered.
	 *
	 * Per RFC 7230, only VISIBLE ASCII characters, spaces, and horizontal
	 * tabs are allowed in values; header continuations MUST consist of
	 * a single CRLF sequence followed by a space or horizontal tab.
	 *
	 * This method filters any values not allowed from the string, and is
	 * lossy.
	 *
	 * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
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
	 * Validate a header value.
	 *
	 * Per RFC 7230, only VISIBLE ASCII characters, spaces, and horizontal
	 * tabs are allowed in values; header continuations MUST consist of
	 * a single CRLF sequence followed by a space or horizontal tab.
	 *
	 * @param   string  $value
	 *
	 * @return  boolean
	 *
	 * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
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
			if (!is_string((string) $value))
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
			$value = is_array($value) ? implode(',', $value) : implode(',', $value);

			$headerArray[] = $key . ': ' . $value;
		}

		if ($toString)
		{
			$headerArray = implode($headerArray, "\r\n");
		}

		return $headerArray;
	}
}
