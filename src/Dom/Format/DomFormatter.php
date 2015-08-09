<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Dom\Format;

/**
 * The HtmlFormatter class.
 *
 * This is a fork from: https://github.com/gajus/dindent to help use test Dom code.
 *
 * @since  {DEPLOY_VERSION}
 */
class DomFormatter
{
	/**
	 * Property log.
	 *
	 * @var  array
	 */
	protected $log = array();

	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = array(
		'indentation_character' => '    '
	);

	/**
	 * Property inlineElements.
	 *
	 * @var  array
	 */
	protected $inlineElements = array(
		'b', 'big', 'i', 'small', 'tt', 'abbr', 'acronym', 'cite', 'code', 'dfn', 'em', 'kbd', 'strong', 'samp',
		'var', 'a', 'bdo', 'br', 'img', 'span', 'sub', 'sup', 'source'
	);

	/**
	 * Property temporaryReplacementsScript.
	 *
	 * @var  array
	 */
	protected $temporaryReplacementsScript = array();

	/**
	 * Property temporaryReplacementsInline.
	 *
	 * @var  array
	 */
	protected $temporaryReplacementsInline = array();

	const ELEMENT_TYPE_BLOCK = 0;
	const ELEMENT_TYPE_INLINE = 1;

	const MATCH_INDENT_NO = 0;
	const MATCH_INDENT_DECREASE = 1;
	const MATCH_INDENT_INCREASE = 2;
	const MATCH_DISCARD = 3;

	/**
	 * Property instance.
	 *
	 * @var  static[]
	 */
	protected static $instance = array();

	/**
	 * getInstance
	 *
	 * @param   string  $type
	 *
	 * @return  static
	 */
	public static function getInstance($type = 'html')
	{
		$type = strtolower($type);

		if (empty(static::$instance[$type]))
		{
			static::$instance[$type] = new static;
		}

		return static::$instance[$type];
	}

	/**
	 * Method to set property instance
	 *
	 * @param   string $type
	 * @param   static $instance
	 *
	 * @return  void
	 */
	public static function setInstance($type, $instance)
	{
		$type = strtolower($type);

		static::$instance[$type] = $instance;
	}

	/**
	 * format
	 *
	 * @param   string  $buffer
	 *
	 * @return  string  Formatted Html string.
	 */
	public static function format($buffer)
	{
		return static::getInstance('html')->indent($buffer);
	}

	/**
	 * formatXml
	 *
	 * @param   string  $buffer
	 *
	 * @return  string
	 */
	public static function formatXml($buffer)
	{
		if (empty(static::$instance['xml']))
		{
			$instance = new static;
			$instance->setInlineElements(array());

			static::$instance['xml'] = $instance;
		}

		return static::getInstance('html')->indent($buffer);
	}

	/**
	 * Constructor.
	 *
	 * @param array $options
	 */
	public function __construct(array $options = array())
	{
		foreach ($options as $name => $value)
		{
			if (!array_key_exists($name, $this->options))
			{
				throw new \InvalidArgumentException('Unrecognized option.');
			}

			$this->options[$name] = $value;
		}
	}

	/**
	 * @param   string   $elementName Element name, e.g. "b".
	 * @param   integer  $type
	 *
	 * @return  void
	 */
	public function setElementType($elementName, $type)
	{
		if ($type === static::ELEMENT_TYPE_BLOCK)
		{
			$this->inlineElements = array_diff($this->inlineElements, array($elementName));
		}
		else
		{
			if ($type === static::ELEMENT_TYPE_INLINE)
			{
				$this->inlineElements[] = $elementName;
			}
			else
			{
				throw new \InvalidArgumentException('Unrecognized element type.');
			}
		}

		$this->inlineElements = array_unique($this->inlineElements);
	}

	/**
	 * Format Html.
	 *
	 * @param string $input HTML input.
	 *
	 * @return string Indented HTML.
	 */
	public function indent($input)
	{
		$this->log = array();

		// Dindent does not indent <script> body. Instead, it temporary removes it from the code,
		// indents the input, and restores the script body.
		if (preg_match_all('/<script\b[^>]*>([\s\S]*?)<\/script>/mi', $input, $matches))
		{
			$this->temporaryReplacementsScript = $matches[0];
			foreach ($matches[0] as $i => $match)
			{
				$input = str_replace($match, '<script>' . ($i + 1) . '</script>', $input);
			}
		}

		/*
		 * Removing double whitespaces to make the source code easier to read.
		 * With exception of <pre>/ CSS white-space changing the default behaviour,
		 * double whitespace is meaningless in HTML output.
		 *
		 * This reason alone is sufficient not to use Dindent in production.
		 */
		$input = str_replace("\t", '', $input);
		$input = preg_replace('/\s{2,}/', ' ', $input);

		// Remove inline elements and replace them with text entities.
		if (preg_match_all('/<(' . implode('|', $this->inlineElements) . ')[^>]*>(?:[^<]*)<\/\1>/', $input, $matches))
		{
			$this->temporaryReplacementsInline = $matches[0];

			foreach ($matches[0] as $i => $match)
			{
				$input = str_replace($match, 'ᐃ' . ($i + 1) . 'ᐃ', $input);
			}
		}

		$subject = $input;

		$output = '';

		$next_line_indentation_level = 0;

		do
		{
			$indentation_level = $next_line_indentation_level;

			$patterns = array(
				// block tag
				'/^(<([a-z]+)(?:[^>]*)>(?:[^<]*)<\/(?:\2)>)/'  => static::MATCH_INDENT_NO,
				// DOCTYPE
				'/^<!([^>]*)>/'                                => static::MATCH_INDENT_NO,
				// tag with implied closing
				'/^<(input|link|meta|base|br|img|hr)([^>]*)>/' => static::MATCH_INDENT_NO,
				// opening tag
				'/^<[^\/]([^>]*)>/'                            => static::MATCH_INDENT_INCREASE,
				// closing tag
				'/^<\/([^>]*)>/'                               => static::MATCH_INDENT_DECREASE,
				// self-closing tag
				'/^<(.+)\/>/'                                  => static::MATCH_INDENT_DECREASE,
				// whitespace
				'/^(\s+)/'                                     => static::MATCH_DISCARD,
				// text node
				'/([^<]+)/'                                    => static::MATCH_INDENT_NO
			);

			$rules = array('NO', 'DECREASE', 'INCREASE', 'DISCARD');

			foreach ($patterns as $pattern => $rule)
			{
				if ($match = preg_match($pattern, $subject, $matches))
				{
					$this->log[] = array(
						'rule'    => $rules[$rule],
						'pattern' => $pattern,
						'subject' => $subject,
						'match'   => $matches[0]
					);

					$subject = mb_substr($subject, mb_strlen($matches[0]));

					if ($rule === static::MATCH_DISCARD)
					{
						break;
					}

					if ($rule === static::MATCH_INDENT_NO)
					{

					}
					else
					{
						if ($rule === static::MATCH_INDENT_DECREASE)
						{
							$next_line_indentation_level--;
							$indentation_level--;
						}
						else
						{
							$next_line_indentation_level++;
						}
					}

					if ($indentation_level < 0)
					{
						$indentation_level = 0;
					}

					$output .= str_repeat($this->options['indentation_character'], $indentation_level) . $matches[0] . "\n";

					break;
				}
			}
		}
		while ($match);

		$interpretedInput = '';

		foreach ($this->log as $e)
		{
			$interpretedInput .= $e['match'];
		}

		if ($interpretedInput !== $input)
		{
			throw new \RuntimeException('Did not reproduce the exact input.');
		}

		$output = preg_replace('/(<(\w+)[^>]*>)\s*(<\/\2>)/', '\\1\\3', $output);

		foreach ($this->temporaryReplacementsScript as $i => $original)
		{
			$output = str_replace('<script>' . ($i + 1) . '</script>', $original, $output);
		}

		foreach ($this->temporaryReplacementsInline as $i => $original)
		{
			$output = str_replace('ᐃ' . ($i + 1) . 'ᐃ', $original, $output);
		}

		return trim($output);
	}

	/**
	 * Debugging utility. Get log for the last indent operation.
	 *
	 * @return array
	 */
	public function getLog()
	{
		return $this->log;
	}

	/**
	 * Method to set property inlineElements
	 *
	 * @param   array $inlineElements
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setInlineElements($inlineElements)
	{
		$this->inlineElements = (array) $inlineElements;

		return $this;
	}
}
