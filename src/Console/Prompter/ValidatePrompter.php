<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Prompter;

use Windwalker\Console\IO\IOInterface;

/**
 * A text prompter but we can set an array to validate input value.
 *
 * @since  2.0
 */
class ValidatePrompter extends CallbackPrompter
{
	/**
	 * The option list to validate input.
	 *
	 * @var array
	 *
	 * @since  2.0
	 */
	protected $options = array();

	/**
	 * Constructor.
	 *
	 * @param   string       $question  The question you want to ask.
	 * @param   array        $options   The option list to validate input.
	 * @param   $default     $default   The default value.
	 * @param   IOInterface  $io        The input object.
	 *
	 * @since   2.0
	 */
	function __construct($question = null, $options = array(),$default = null, IOInterface $io = null)
	{
		$this->options = $options;

		parent::__construct($question, $default, $io);
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
	 * @since   2.0
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
	 * @since   2.0
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
	 * @since   2.0
	 */
	public function setOptions($options)
	{
		$this->options = $options;

		return $this;
	}
}

