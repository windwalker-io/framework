<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * HTML Helper to handle some text.
 *
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 */
class AKHelperHtml
{
	/**
	 * Repair HTML. If Tidy not exists, use repair function.
	 *
	 * @param   string  $html     The HTML string to repair.
	 * @param   boolean $use_tidy Force tidy or not.
	 *
	 * @return  string  Repaired HTML.
	 */
	public static function repair($html, $use_tidy = true)
	{

		if (function_exists('tidy_repair_string') && $use_tidy)
		{
			$TidyConfig = array('indent'         => true,
			                    'output-xhtml'   => true,
			                    'show-body-only' => true,
			                    'wrap'           => false
			);

			return tidy_repair_string($html, $TidyConfig, 'utf8');
		}
		else
		{
			$arr_single_tags = array('meta', 'img', 'br', 'link', 'area');

			//put all opened tags into an array
			preg_match_all("#<([a-z]+)( .*)?(?!/)>#iU", $html, $result);
			$openedtags = $result[1];

			//put all closed tags into an array
			preg_match_all("#</([a-z]+)>#iU", $html, $result);
			$closedtags = $result[1];
			$len_opened = count($openedtags);

			// all tags are closed
			if (count($closedtags) == $len_opened)
			{
				return $html;
			}

			$openedtags = array_reverse($openedtags);

			// close tags
			for ($i = 0; $i < $len_opened; $i++)
			{
				if (!in_array($openedtags[$i], $closedtags))
				{
					if (!in_array($openedtags[$i], $arr_single_tags))
					{
						$html .= "</" . $openedtags[$i] . ">";
					}
				}
				else
				{
					unset ($closedtags[array_search($openedtags[$i], $closedtags)]);
				}
			}

			return $html;
		}
	}

	/**
	 * Parse Markdown and convert to HTML.
	 * Use PHP Markdown & Markdown Extra: http://michelf.ca/projects/php-markdown/
	 *
	 * @param    string $text  Text to parse Markdown.
	 * @param    string $extra Use MarkdownExtra: http://michelf.ca/projects/php-markdown/extra/ .
	 *
	 * @return   string     Parsed Text.
	 */
	public static function markdown($text, $extra = true, $option = array())
	{
		require_once AKPATH_HTML . "/php-markdown/Markdown.php";

		$text = str_replace("\t", '    ', $text);

		if ($extra)
		{
			require_once AKPATH_HTML . "/php-markdown/MarkdownExtra.php";
			$result = Michelf\MarkdownExtra::defaultTransform($text);
		}
		else
		{
			$result = Michelf\Markdown::defaultTransform($text);
		}

		if (JArrayHelper::getValue($option, 'highlight_enable', 1))
		{
			self::highlight(JArrayHelper::getValue($option, 'highlight', 'default'));
		}

		return $result;
	}

	/**
	 * Highlight Markdown <pre><code class="lang">.
	 * Use highlight.js: http://softwaremaniacs.org/soft/highlight/en/
	 *
	 * @param   string $theme Code style name.
	 */
	public static function highlight($theme = 'default')
	{
		static $loaded;

		if (!$loaded)
		{
			$css = '/assets/js/highlight/styles/' . $theme . '.css';

			if (!JFile::exists(AKPATH_ROOT . $css))
			{
				$css = '/assets/js/highlight/styles/default.css';
			}

			$doc = JFactory::getDocument();
			$doc->addStylesheet(AKHelper::_('path.getWWUrl') . $css);
			$doc->addScript(AKHelper::_('path.getWWUrl') . '/assets/js/highlight/highlight.pack.js');

			$doc->addScriptDeclaration("\n    hljs.initHighlightingOnLoad();");
			$loaded = true;
		}
	}

	/**
	 * Internal method to get a JavaScript object notation string from an array
	 *
	 * @param   array $array The array to convert to JavaScript object notation
	 *
	 * @return  string  JavaScript object notation representation of the array
	 */
	public static function getJSObject(array $array = array())
	{
		$object = '{';

		// Iterate over array to build objects
		foreach ((array) $array as $k => $v)
		{
			if (is_null($v))
			{
				continue;
			}

			if (is_bool($v))
			{
				$object .= ' ' . $k . ': ';
				$object .= ($v) ? 'true' : 'false';
				$object .= ',';
			}
			elseif (!is_array($v) && !is_object($v))
			{
				$object .= ' ' . $k . ': ';
				$object .= (is_numeric($v) || strpos($v, '\\') === 0) ? (is_numeric($v)) ? $v : substr($v, 1) : "'" . str_replace("'", "\\'", trim($v, "'")) . "'";
				$object .= ',';
			}
			else
			{
				$object .= ' ' . $k . ': ' . self::getJSObject($v) . ',';
			}
		}

		if (substr($object, -1) == ',')
		{
			$object = substr($object, 0, -1);
		}

		$object .= '}';

		return $object;
	}
}
