<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Uri;

/**
 * Uri Helper
 *
 * This class provides an UTF-8 safe version of parse_url().
 *
 * This class is a fork from Joomla Uri.
 *
 * @since  {DEPLOY_VERSION}
 */
class UriHelper
{
	/**
	 * Does a UTF-8 safe version of PHP parse_url function
	 *
	 * @param   string  $url  URL to parse
	 *
	 * @return  mixed  Associative array or false if badly formed URL.
	 *
	 * @see     http://us3.php.net/manual/en/function.parse-url.php
	 * @since   {DEPLOY_VERSION}
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
}
