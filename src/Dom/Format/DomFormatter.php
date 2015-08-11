<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Dom\Format;

/**
 * The AbstractFormatter class.
 *
 *  This class based on https://github.com/gajus/dindent to help use test Dom code.
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
	protected static $instance;

	/**
	 * getInstance
	 *
	 * @return  static
	 */
	public static function getInstance()
	{
		if (empty(static::$instance))
		{
			static::$instance = new static;
		}

		return static::$instance;
	}

	/**
	 * Method to set property instance
	 *
	 * @param   static $instance
	 *
	 * @return  void
	 */
	public static function setInstance($instance)
	{
		static::$instance = $instance;
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
		return static::getInstance()->indent($buffer);
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
	 * indent
	 *
	 * @param   string  $input
	 *
	 * @return  string
	 */
	public function indent($input)
	{
		$this->log = array();

		$input = $this->removeDoubleWhiteSpace($input);

		$output = $this->doIndent($input);

		return trim($output);
	}

	/**
	 * Format Dom.
	 *
	 * @param string $input Dom input.
	 *
	 * @return string Indented Dom.
	 */
	public function doIndent($input)
	{
		$subject = $input;

		$output = '';

		$nextLineIndentationLevel = 0;

		do
		{
			$indentationLevel = $nextLineIndentationLevel;

			$patterns = $this->getTagPatterns();

			$rules = array('NO', 'DECREASE', 'INCREASE', 'DISCARD');

			$match = array();

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
							$nextLineIndentationLevel--;
							$indentationLevel--;
						}
						else
						{
							$nextLineIndentationLevel++;
						}
					}

					if ($indentationLevel < 0)
					{
						$indentationLevel = 0;
					}

					$output .= str_repeat($this->options['indentation_character'], $indentationLevel) . $matches[0] . "\n";

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

		return $output;
	}

	/**
	 * removeDoubleWhiteSpace
	 *
	 * @param  string $input
	 *
	 * @return  string
	 */
	protected function removeDoubleWhiteSpace($input)
	{
		/*
		 * Removing double whitespaces to make the source code easier to read.
		 * With exception of <pre>/ CSS white-space changing the default behaviour,
		 * double whitespace is meaningless in HTML output.
		 *
		 * This reason alone is sufficient not to use Dindent in production.
		 */
		$input = str_replace("\t", '', $input);
		$input = preg_replace('/\s{2,}/', ' ', $input);

		return $input;
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
	 * Debugging utility. Get log for the last indent operation.
	 *
	 * @return array
	 */
	public function getLog()
	{
		return $this->log;
	}
}
