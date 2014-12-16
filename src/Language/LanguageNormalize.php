<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Language;

/**
 * Class LanguageNormalize
 *
 * @since 2.0
 */
abstract class LanguageNormalize
{
	/**
	 * toLanguageKey
	 *
	 * @param string $lang
	 *
	 * @return  string
	 */
	public static function toLanguageTag($lang)
	{
		$lang = str_replace('_', '-', $lang);

		$lang = explode('-', $lang);

		if (isset($lang[1]))
		{
			$lang[1] = strtoupper($lang[1]);
		}

		$lang[0] = strtolower($lang[0]);

		return implode('-', $lang);
	}

	/**
	 * getLocaliseClassPrefix
	 *
	 * @param string $lang
	 *
	 * @return  string
	 */
	public static function getLocaliseClassPrefix($lang)
	{
		$lang = static::toLanguageTag($lang);

		$lang = str_replace('-', '', $lang);

		return ucfirst($lang);
	}

	/**
	 * toLanguageKey
	 *
	 * @param string $key
	 *
	 * @return  string
	 */
	public static function toLanguageKey($key)
	{
		// Only allow A-Z a-z 0-9 and "_", other characters will be replace with "_".
		$key = preg_replace('/[^A-Z0-9]+/i', '.', $key);

		return strtolower(trim($key, '.'));
	}
}

