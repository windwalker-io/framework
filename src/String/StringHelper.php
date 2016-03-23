<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\String;

use Windwalker\Utilities\ArrayHelper;

/**
 * The StringHelper class.
 *
 * @since  2.0.8
 */
abstract class StringHelper
{
	const INCREMENT_STYLE_DASH = 'dash';
	const INCREMENT_STYLE_DEFAULT = 'default';

	/**
	 * Increment styles.
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected static $incrementStyles = array(
		self::INCREMENT_STYLE_DASH => array(
			'#-(\d+)$#',
			'-%d'
		),
		self::INCREMENT_STYLE_DEFAULT => array(
			array('#\((\d+)\)$#', '#\(\d+\)$#'),
			array(' (%d)', '(%d)'),
		),
	);

	/**
	 * isEmptyString
	 *
	 * @param string $string
	 *
	 * @return  boolean
	 */
	public static function isEmpty($string)
	{
		if (is_array($string) || is_object($string))
		{
			return empty($string);
		}

		$string = (string) $string;

		return !(boolean) strlen($string);
	}

	/**
	 * isZero
	 *
	 * @param string $string
	 *
	 * @return  boolean
	 */
	public static function isZero($string)
	{
		return $string === '0' || $string === 0;
	}

	/**
	 * Quote a string.
	 *
	 * @param   string $string The string to quote.
	 * @param   array  $quote  The quote symbol.
	 *
	 * @return  string Quoted string.
	 */
	public static function quote($string, $quote = array('"', '"'))
	{
		$quote = (array) $quote;

		if (empty($quote[1]))
		{
			$quote[1] = $quote[0];
		}

		return $quote[0] . $string . $quote[1];
	}

	/**
	 * Back quote a string.
	 *
	 * @param   string $string The string to quote.
	 *
	 * @return  string Quoted string.
	 */
	public static function backquote($string)
	{
		return static::quote($string, '`');
	}

	/**
	 * Parse variable and replace it. This method is a simple template engine.
	 *
	 * Example: The {{ foo.bar.yoo }} will be replace to value of `$data['foo']['bar']['yoo']`
	 *
	 * @param   string $string The template to replace.
	 * @param   array  $data   The data to find.
	 * @param   array  $tags   The variable tags.
	 *
	 * @return  string Replaced template.
	 *
	 * @deprecated  3.0  Use SimpleTemplate::render() instead.
	 */
	public static function parseVariable($string, $data = array(), $tags = array('{{', '}}'))
	{
		return SimpleTemplate::render($string, $data, $tags);
	}

	/**
	 * Increments a trailing number in a string.
	 *
	 * Used to easily create distinct labels when copying objects. The method has the following styles:
	 *
	 * default: "Label" becomes "Label (2)"
	 * dash:    "Label" becomes "Label-2"
	 *
	 * @param   string   $string  The source string.
	 * @param   string   $style   The the style (default|dash).
	 * @param   integer  $n       If supplied, this number is used for the copy, otherwise it is the 'next' number.
	 *
	 * @return  string  The incremented string.
	 *
	 * @since   2.0
	 */
	public static function increment($string, $style = self::INCREMENT_STYLE_DEFAULT, $n = 0)
	{
		$styleSpec = isset(self::$incrementStyles[$style]) ? self::$incrementStyles[$style] : self::$incrementStyles['default'];

		// Regular expression search and replace patterns.
		if (is_array($styleSpec[0]))
		{
			$rxSearch = $styleSpec[0][0];
			$rxReplace = $styleSpec[0][1];
		}
		else
		{
			$rxSearch = $rxReplace = $styleSpec[0];
		}

		// New and old (existing) sprintf formats.
		if (is_array($styleSpec[1]))
		{
			$newFormat = $styleSpec[1][0];
			$oldFormat = $styleSpec[1][1];
		}
		else
		{
			$newFormat = $oldFormat = $styleSpec[1];
		}

		// Check if we are incrementing an existing pattern, or appending a new one.
		if (preg_match($rxSearch, $string, $matches))
		{
			$n = empty($n) ? ($matches[1] + 1) : $n;
			$string = preg_replace($rxReplace, sprintf($oldFormat, $n), $string);
		}
		else
		{
			$n = empty($n) ? 2 : $n;
			$string .= sprintf($newFormat, $n);
		}

		return $string;
	}

	/**
	 * at
	 *
	 * @param string $string
	 * @param int    $num
	 *
	 * @return  string
	 */
	public static function at($string, $num)
	{
		$num = (int) $num;

		if (Utf8String::strlen($string) < $num)
		{
			return null;
		}

		return Utf8String::substr($string, $num, 1);
	}

	/**
	 * remove spaces
	 *
	 * See: http://stackoverflow.com/questions/3760816/remove-new-lines-from-string
	 * And: http://stackoverflow.com/questions/9558110/php-remove-line-break-or-cr-lf-with-no-success
	 *
	 * @param string $string
	 *
	 * @return  string
	 */
	public static function collapseWhitespace($string)
	{
		$string = preg_replace('/\s\s+/', ' ', $string);

		return trim(preg_replace('/\s+/', ' ', $string));
	}

	/**
	 * endsWith
	 *
	 * @param string  $string
	 * @param string  $target
	 * @param boolean $caseSensitive
	 *
	 * @return  boolean
	 */
	public static function endsWith($string, $target, $caseSensitive = true)
	{
		$stringLength = Utf8String::strlen($string);
		$targetLength = Utf8String::strlen($target);

		if ($stringLength < $targetLength)
		{
			return false;
		}

		if (!$caseSensitive)
		{
			$string = strtolower($string);
			$target = strtolower($target);
		}

		$end = Utf8String::substr($string, -$targetLength);

		return $end === $target;
	}

	/**
	 * startsWith
	 *
	 * @param string  $string
	 * @param string  $target
	 * @param boolean $caseSensitive
	 *
	 * @return  boolean
	 */
	public static function startsWith($string, $target, $caseSensitive = true)
	{
		if (!$caseSensitive)
		{
			$string = strtolower($string);
			$target = strtolower($target);
		}

		return strpos($string, $target) === 0;
	}

	/**
	 * Explode a string and force elements number.
	 *
	 * @param string $separator
	 * @param string $data
	 * @param int    $number
	 * @param string $callback
	 *
	 * @return array
	 */
	public static function explode($separator, $data, $number = null, $callback = 'array_push')
	{
		if ($number)
		{
			$array = explode($separator, $data, $number);
		}
		else
		{
			$array = explode($separator, $data);
		}

		if (count($array) < $number)
		{
			foreach (range(1, $number - count($array)) as $i)
			{
				$callback($array, null);
			}
		}

		return $array;
	}
}
