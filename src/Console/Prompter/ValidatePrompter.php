<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Prompter;

use Joomla\Input;
use Joomla\Application\Cli\Output\Stdout;

/**
 * A text prompter but we can set an array to validate input value.
 *
 * @since  1.0
 */
class ValidatePrompter extends CallbackPrompter
{
	/**
	 * The option list to validate input.
	 *
	 * @var array
	 *
	 * @since  1.0
	 */
	protected $options = array();

	/**
	 * Constructor.
	 *
	 * @param   string     $question  The question you want to ask.
	 * @param   $default   $default   The default value.
	 * @param   array      $options   The option list to validate input.
	 * @param   Input\Cli  $input     The input object.
	 * @param   Stdout     $output    The output object.
	 *
	 * @since   1.0
	 */
	function __construct($question = null, $default = null, $options = array(), Input\Cli $input = null, Stdout $output = null)
	{
		$this->options = $options;

		parent::__construct($question, $default, $input, $output);
	}

	/**
	 * Get callable handler.
	 *
	 * @return  callable  The validate callback.
	 *
	 * @since   1.0
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
			if (in_array($value, $options))
			{
				return true;
			}

			return false;
		};
	}

	/**
	 * Add an option.
	 *
	 * @param   string  $description  Option description.
	 * @param   string  $option       Option key, if this param is NULL, will use int as option key.
	 *
	 * @return  ValidatePrompter  Return self to support chaining.
	 *
	 * @since   1.0
	 */
	public function addOption($description, $option = null)
	{
		if ($option)
		{
			$this->options[$option] = $description;
		}
		else
		{
			$this->options[] = $description;
		}

		return $this;
	}

	/**
	 * Remove an option by key.
	 *
	 * @param   mixed  $key  The option key you want to remove.
	 *
	 * @return  ValidatePrompter  Return self to support chaining.
	 *
	 * @since   1.0
	 */
	public function removeOption($key)
	{
		if (!empty($this->options[$key]))
		{
			unset($this->options[$key]);
		}

		return $this;
	}

	/**
	 * Set option list.
	 *
	 * @param   array  $options  The option list.
	 *
	 * @return  ValidatePrompter  Return self to support chaining.
	 *
	 * @since   1.0
	 */
	public function setOptions($options)
	{
		$this->options = $options;

		return $this;
	}
}

