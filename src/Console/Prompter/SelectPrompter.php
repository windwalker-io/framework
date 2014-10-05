<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Prompter;

/**
 * A prompter supports select list.
 *
 * @since  {DEPLOY_VERSION}
 */
class SelectPrompter extends ValidatePrompter
{
	/**
	 * Property listTemplate.
	 *
	 * @var  string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $listTemplate = " %-{WIDTH}s[%s] - %s";

	/**
	 * Returning message if valid fail.
	 *
	 * @var  string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $noValidMessage = '  Not a valid selection';

	/**
	 * Returning message if valid fail and close.
	 *
	 * @var  string
	 *
	 * @since  {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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

