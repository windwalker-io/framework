<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Http\Helper;

use Psr\Http\Message\UploadedFileInterface;

/**
 * The ServerHelper class.
 * 
 * @since  2.1
 */
abstract class ServerHelper
{
	/**
	 * Access a value in an array, returning a default value if not found
	 *
	 * Will also do a case-insensitive search if a case sensitive search fails.
	 *
	 * @param   array   $servers  Server values to search.
	 * @param   string  $name     The name we want to search.
	 * @param   mixed   $default  Default value if not found.
	 *
	 * @return  mixed
	 */
	public static function getValue(array $servers, $name, $default = null)
	{
		if (array_key_exists($name, $servers))
		{
			return $servers[$name];
		}

		return $default;
	}

	/**
	 * Recursively validate the structure in an uploaded files array.
	 *
	 * Every file should be an UploadedFileInterface object.
	 *
	 * @param   array  $files  Files array.
	 *
	 * @return  boolean
	 */
	public static function validateUploadedFiles(array $files)
	{
		foreach ($files as $file)
		{
			if (is_array($file))
			{
				static::validateUploadedFiles($file);

				continue;
			}

			if (! $file instanceof UploadedFileInterface)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * A proxy to getallheaders().
	 * 
	 * If this function not available, we will use native code to implement this function.
	 *
	 * @return  array|false
	 */
	public static function getAllHeaders()
	{
		if (function_exists('getallheaders'))
		{
			return getallheaders();
		}

		$headers = array();

		foreach ($_SERVER as $name => $value)
		{
			if (substr($name, 0, 5) == 'HTTP_')
			{
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		
		return $headers;
	}

	/**
	 * A proxy to apache_request_headers().
	 *
	 * If this function not available, we will use native code to implement this function.
	 *
	 * @return  array
	 *
	 * @link  http://php.net/manual/en/function.getallheaders.php#99814
	 */
	public static function apacheRequestHeaders()
	{
		if (function_exists('apache_request_headers'))
		{
			return apache_request_headers();
		}
		
		$out = array();

		foreach ($_SERVER as $key => $value)
		{
			if (substr($key, 0, 5) == "HTTP_")
			{
				$key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));

				$out[$key] = $value;
			}
			else
			{
				$out[$key] = $value;
			}
		}

		return $out;
	}
}
