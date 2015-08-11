<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Dom\Format;

/**
 * The HtmlFormatter class.
 *
 * @since  2.1
 */
class HtmlFormatter extends DomFormatter
{
	/**
	 * Property inlineElements.
	 *
	 * @var  array
	 */
	protected $inlineElements = array(
		'b', 'big', 'i', 'small', 'tt', 'abbr', 'acronym', 'cite', 'code', 'dfn', 'em', 'kbd', 'strong', 'samp',
		'var', 'a', 'bdo', 'br', 'img', 'span', 'sub', 'sup'
	);

	/**
	 * Property unpairedElements.
	 *
	 * @var  array
	 */
	protected $unpairedElements = array(
		'img', 'br', 'hr', 'area', 'param', 'wbr', 'base', 'link', 'meta', 'input', 'option', 'a', 'source'
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

	/**
	 * indent
	 *
	 * @param   string  $input
	 *
	 * @return  string
	 */
	public function indent($input)
	{
		$this->log = array();

		$input = $this->tempScripts($input);

		$input = $this->removeDoubleWhiteSpace($input);

		$input = $this->tempInlineElements($input);

		$output = $this->doIndent($input);

		$output = $this->restoreScripts($output);

		$output = $this->restoreInlineElements($output);

		return trim($output);
	}

	/**
	 * tempScripts
	 *
	 * @param   string  $input
	 *
	 * @return  string
	 */
	protected function tempScripts($input)
	{
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

		return $input;
	}

	/**
	 * restoreScripts
	 *
	 * @param   string $output
	 *
	 * @return  string
	 */
	protected function restoreScripts($output)
	{
		foreach ($this->temporaryReplacementsScript as $i => $original)
		{
			$output = str_replace('<script>' . ($i + 1) . '</script>', $original, $output);
		}

		return $output;
	}

	/**
	 * tempInlineElements
	 *
	 * @param   string $input
	 *
	 * @return  string
	 */
	protected function tempInlineElements($input)
	{
		// Remove inline elements and replace them with text entities.
		if (preg_match_all('/<(' . implode('|', $this->inlineElements) . ')[^>]*>(?:[^<]*)<\/\1>/', $input, $matches))
		{
			$this->temporaryReplacementsInline = $matches[0];

			foreach ($matches[0] as $i => $match)
			{
				$input = str_replace($match, 'ᐃ' . ($i + 1) . 'ᐃ', $input);
			}
		}

		return $input;
	}

	/**
	 * restoreInlineElements
	 *
	 * @param   string  $output
	 *
	 * @return  string
	 */
	protected function restoreInlineElements($output)
	{
		foreach ($this->temporaryReplacementsInline as $i => $original)
		{
			$output = str_replace('ᐃ' . ($i + 1) . 'ᐃ', $original, $output);
		}

		return $output;
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

	/**
	 * getTagPatterns
	 *
	 * @return  array
	 */
	protected function getTagPatterns()
	{
		return array(
			// block tag
			'/^(<([a-z]+)(?:[^>]*)>(?:[^<]*)<\/(?:\2)>)/'  => static::MATCH_INDENT_NO,
			// DOCTYPE
			'/^<!([^>]*)>/'                                => static::MATCH_INDENT_NO,
			// tag with implied closing
			'/^<(' . $this->getUnpairedElements(true) . ')([^>]*)>/' => static::MATCH_INDENT_NO,
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
	 * addInlineElement
	 *
	 * @param   string $element
	 *
	 * @return  static
	 */
	public function addInlineElement($element)
	{
		$this->inlineElements[] = trim(strtolower($element));

		return $this;
	}

	/**
	 * addUnpairedElement
	 *
	 * @param   string $element
	 *
	 * @return  static
	 */
	public function addUnpairedElement($element)
	{
		$this->unpairedElements[] = trim(strtolower($element));

		return $this;
	}

	/**
	 * Method to set property unpairedElements
	 *
	 * @param   array $unpairedElements
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setUnpairedElements($unpairedElements)
	{
		$this->unpairedElements = (array) $unpairedElements;

		return $this;
	}

	/**
	 * Method to get property UnpairedElements
	 *
	 * @return  array
	 */
	public function getUnpairedElements($implode = false)
	{
		return $implode ? implode('|', $this->unpairedElements) : $this->unpairedElements;
	}
}
