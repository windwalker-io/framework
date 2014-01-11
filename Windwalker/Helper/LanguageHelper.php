<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Helper;

use JFactory;
use JFolder;

/**
 * Class LanguageHelper
 *
 * @since 1.0
 */
class LanguageHelper
{
	/**
	 * An API key for Google translate.
	 *
	 * @var string
	 */
	const APT_KEY = 'AIzaSyC04nF4KXjfR2VQ0jsFm5vEd9LbyiXqbKw';

	/**
	 * Translate a long text by Google, if it too long, will separate it..
	 *
	 * @param   string  $text      String to translate.
	 * @param   string  $SourceLan Translate from this language, eg: 'zh-tw'. Empty will auto detect.
	 * @param   string  $ResultLan Translate to this language, eg: 'en'. Empty will auto detect.
	 * @param   integer $separate  Separate text by a number of words, batch translate them and re combine to return.
	 *
	 * @return  string    Translated text.
	 */
	public static function translate($text, $SourceLan = null, $ResultLan = null, $separate = 0)
	{
		// If text too big, separate it.
		if ($separate)
		{
			if (\JString::strlen($text) > $separate)
			{
				$text = \JString::str_split($text, $separate);
			}
			else
			{
				$text = array($text);
			}
		}
		else
		{
			$text = array($text);
		}

		$result = '';

		// Do translate by google translate API.
		foreach ($text as $txt)
		{
			$result .= self::gTranslate($txt, $SourceLan, $ResultLan);
		}

		return $result;
	}

	/**
	 * A method to do Google translate.
	 *
	 * @param   string $text      String to translate.
	 * @param   string $SourceLan Translate from this language, eg: 'zh-tw'. Empty will auto detect.
	 * @param   string $ResultLan Translate to this language, eg: 'en'. Empty will auto detect.
	 *
	 * @return  string|bool Translated text.
	 */
	public static function gTranslate($text, $SourceLan, $ResultLan)
	{
		$url = new \JUri;

		// For Google APIv2
		$url->setHost('https://www.googleapis.com/');
		$url->setPath('language/translate/v2');

		$query['key']    = self::APT_KEY;
		$query['q']      = urlencode($text);
		$query['source'] = $SourceLan;
		$query['target'] = $ResultLan;

		if (!$text)
		{
			return false;
		}

		$url->setQuery($query);
		$url->toString();
		$response = CurlHelper::get((string) $url);

		if (empty($response->body))
		{
			return '';
		}

		$json = new \JRegistry;
		$json->loadString($response->body, 'json');

		$r = $json->get('data.translations');

		return $r[0]->translatedText;
	}

	/**
	 * Load all language files from component.
	 *
	 * @param   string $lang   Language tag.
	 * @param   string $option Component option.
	 *
	 * @return  boolean
	 */
	public static function loadAll($lang = 'en-GB', $option = null)
	{
		$folder = PathHelper::getAdmin($option) . '/language/' . $lang;

		if (is_dir($folder))
		{
			$files = JFolder::files($folder);
		}
		else
		{
			return false;
		}

		$lang  = JFactory::getLanguage();

		foreach ($files as $file)
		{
			$file = explode('.', $file);

			if (array_pop($file) != 'ini')
			{
				continue;
			}

			array_shift($file);

			if (count($file) == 1 || $file[1] == 'sys')
			{
				continue;
			}

			$lang->load(implode('.', $file), PathHelper::getAdmin($option));
		}

		return true;
	}

	/**
	 * Load language from an extension.
	 *
	 * @param   string $ext    Extension element name, eg: com_content, plg_group_name.
	 * @param   string $client site or admin.
	 *
	 * @return  boolean
	 */
	public static function loadLanguage($ext, $client = 'site')
	{
		$lang = JFactory::getLanguage();

		return $lang->load($ext, JPATH_BASE, null, false, false)
			|| $lang->load($ext, PathHelper::get($ext, $client), null, false, false)
			|| $lang->load($ext, JPATH_BASE, null, true)
			|| $lang->load($ext, PathHelper::get($ext, $client), null, true);
	}
}
