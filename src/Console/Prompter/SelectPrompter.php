<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Prompter;

/**
 * A prompter supports select list.
 *
 * @since  2.0
 */
class SelectPrompter extends ValidatePrompter
{
	/**
	 * Property listTemplate.
	 *
	 * @var  string
	 *
	 * @since  2.0
	 */
	protected $listTemplate = " %-{WIDTH}s[%s] - %s";

	/**
	 * Returning message if valid fail.
	 *
	 * @var  string
	 *
	 * @since  2.0
	 */
	protected $noValidMessage = '  Not a valid selection';

	/**
	 * Returning message if valid fail and close.
	 *
	 * @var  string
	 *
	 * @since  2.0
	 */
	protected $closeMessage = 'No selected and close.';

	/**
	 * Show prompt to ask user.
	 *
	 * @param   string  $msg      Question.
	 * @param   string  $default  Default value.
	 *
	 * @return  string  The value that use input.
	 *
	 * @since   2.0
	 */
	public function ask($msg = '', $default = null)
	{
		$this->io->out()->out($this->renderList());

		return parent::ask($msg, $default);
	}

	/**
	 * Render select option list.
	 *
	 * @return  string  list string.
	 *
	 * @since   2.0
	 */
	protected function renderList()
	{
		$list        = '';
		$alignSpaces = 8;

		// Count key length
		$keys    = array_keys($this->options);
		$lengths = array_map('strlen', $keys);
		$longest = max($lengths);
		$longest = $longest >= $alignSpaces ? $alignSpaces : $longest;

		// Build select list.
		foreach ($this->options as $key => $description)
		{
			$tmpl = str_replace('{WIDTH}', $longest, $this->listTemplate);

			$list .= sprintf($tmpl, ' ', $key, $description) . "\n";
		}

		return rtrim($list);
	}

	/**
	 * Get callable handler.
	 *
	 * @return  callable  The validate callback.
	 *
	 * @since   2.0
	 */
	public function getHandler()
	{
		if (is_callable($this->handler))
		{
			return $this->handler;
		}

		$options = $this->options;

		return function($value) use ($options)
		{
			if (array_key_exists($value, $options))
			{
				return true;
			}

			return false;
		};
	}
}

